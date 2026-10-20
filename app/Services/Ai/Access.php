<?php

namespace App\Services\Ai;

use App\Models\AiAccessGrant;
use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnitCoordinator;
use App\Policies\ReportEvaluationPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use App\Services\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;

class Access
{
    public function accountAllows(User $user, string $dataset): bool
    {
        if (! app(ReportPolicy::class)->viewAny($user) || ! app(ReportPolicy::class)->view($user, new Report)) {
            return false;
        }
        if (isset(Registry::REFERENCES[$dataset])) {
            [$model] = Registry::REFERENCES[$dataset];
            $policy = app('App\\Policies\\'.class_basename($model).'Policy');

            return $policy->viewAny($user) && $policy->view($user, new $model);
        }

        return match ($dataset) {
            'reports', 'documentation_metadata' => true,
            'people' => app(UserPolicy::class)->viewAny($user) && app(UserPolicy::class)->view($user, new User),
            'evaluations' => app(OrganizationContext::class)->monevEnabled(),
            default => false,
        };
    }

    public function effective(User $user, AiAccessGrant $grant): array
    {
        $definitions = app(Registry::class)->definitions();
        $fields = [];
        foreach ($grant->datasets as $dataset) {
            if (isset($definitions[$dataset]) && $this->accountAllows($user, $dataset)) {
                $allowed = array_values(array_intersect($grant->fields[$dataset] ?? [], array_keys($definitions[$dataset])));
                if ($allowed !== []) {
                    $fields[$dataset] = $allowed;
                }
            }
        }

        return $fields;
    }

    public function reports(User $user, AiAccessGrant $grant): Builder
    {
        $query = Report::query();
        if (! $this->accountAllows($user, 'reports')) {
            return $query->whereRaw('1 = 0');
        }
        if ($grant->report_scope !== 'account') {
            $query->where(fn (Builder $q) => $q->where('user_id', $user->id)
                ->orWhereHas('followers', fn (Builder $followers) => $followers->whereKey($user->id)));
        }

        return $query;
    }

    public function evaluations(User $user, AiAccessGrant $grant): Builder
    {
        $query = ReportEvaluation::query()->whereIn('report_id', $this->reports($user, $grant)->select('reports.id'))
            ->whereHas('workUnit');
        if (! $this->accountAllows($user, 'evaluations')) {
            return $query->whereRaw('1 = 0');
        }
        $policy = app(ReportEvaluationPolicy::class);
        if ($user->hasRole(['admin', 'super_admin']) || $user->can($policy::MANAGE_ALL)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->whereHas('report', fn (Builder $r) => $r->where('user_id', $user->id)
                ->orWhereHas('followers', fn (Builder $f) => $f->whereKey($user->id)))
                ->orWhereHas('workUnit', function (Builder $unit) use ($user): void {
                    $latest = WorkUnitCoordinator::query()->select('user_id')->whereColumn('work_unit_id', 'work_units.id')
                        ->whereDate('starts_at', '<=', today())
                        ->where(fn (Builder $active) => $active->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
                        ->orderByDesc('starts_at')->orderByDesc('id')->limit(1);
                    $unit->where($latest, '=', $user->id);
                });
        });
    }
}
