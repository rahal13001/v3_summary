<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnit;

class ReportEvaluationPolicy
{
    public const MANAGE_ALL = 'manage_all_report_evaluations';

    public const EXPORT = 'export_report_evaluations';

    public function view(User $user, ReportEvaluation $evaluation): bool
    {
        return $this->canManage($user, $evaluation->report, $evaluation->workUnit);
    }

    public function createFor(User $user, Report $report, WorkUnit $workUnit): bool
    {
        return $this->canManage($user, $report, $workUnit);
    }

    public function canManageReport(User $user, Report $report): bool
    {
        if ($this->hasGlobalAccess($user) || (string) $report->user_id === (string) $user->getKey()) {
            return true;
        }

        if ($report->followers()->whereKey($user)->exists()) {
            return true;
        }

        return $report->workUnits()
            ->whereHas('coordinatorAssignments', fn ($query) => $query
                ->where('user_id', $user->getKey())
                ->whereDate('starts_at', '<=', today())
                ->where(fn ($query) => $query
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', today())))
            ->exists();
    }

    public function update(User $user, ReportEvaluation $evaluation): bool
    {
        return $this->canManage($user, $evaluation->report, $evaluation->workUnit);
    }

    public function viewHistory(User $user, ReportEvaluation $evaluation): bool
    {
        return $this->view($user, $evaluation);
    }

    public function delete(User $user, ReportEvaluation $evaluation): bool
    {
        return false;
    }

    public function export(User $user, WorkUnit $workUnit): bool
    {
        if (! $user->can(self::EXPORT)) {
            return false;
        }

        return $this->hasGlobalAccess($user) || $this->coordinates($user, $workUnit);
    }

    private function canManage(User $user, Report $report, WorkUnit $workUnit): bool
    {
        if ($this->hasGlobalAccess($user)) {
            return true;
        }

        if ((string) $report->user_id === (string) $user->getKey()) {
            return true;
        }

        if ($report->followers()->whereKey($user)->exists()) {
            return true;
        }

        return $this->coordinates($user, $workUnit);
    }

    private function hasGlobalAccess(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']) || $user->can(self::MANAGE_ALL);
    }

    private function coordinates(User $user, WorkUnit $workUnit): bool
    {
        return (string) $workUnit->currentCoordinatorAssignment?->user_id === (string) $user->getKey();
    }
}
