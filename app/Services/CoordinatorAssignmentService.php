<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkUnit;
use App\Models\WorkUnitCoordinator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoordinatorAssignmentService
{
    public function assign(
        WorkUnit $workUnit,
        User $user,
        mixed $startsAt,
        mixed $endsAt = null,
    ): WorkUnitCoordinator {
        return DB::transaction(function () use ($workUnit, $user, $startsAt, $endsAt): WorkUnitCoordinator {
            WorkUnit::query()->whereKey($workUnit)->lockForUpdate()->firstOrFail();

            [$start, $end] = $this->normalizedPeriod($startsAt, $endsAt);
            $this->assertAvailable($workUnit, $start, $end);

            return WorkUnitCoordinator::query()->create([
                'work_unit_id' => $workUnit->getKey(),
                'user_id' => $user->getKey(),
                'starts_at' => $start,
                'ends_at' => $end,
            ]);
        });
    }

    public function update(WorkUnitCoordinator $assignment, array $data): WorkUnitCoordinator
    {
        return DB::transaction(function () use ($assignment, $data): WorkUnitCoordinator {
            WorkUnit::query()->whereKey($assignment->work_unit_id)->lockForUpdate()->firstOrFail();

            [$start, $end] = $this->normalizedPeriod($data['starts_at'], $data['ends_at'] ?? null);
            $this->assertAvailable($assignment->workUnit, $start, $end, $assignment);

            $assignment->update([
                'user_id' => $data['user_id'],
                'starts_at' => $start,
                'ends_at' => $end,
            ]);

            return $assignment;
        });
    }

    private function assertAvailable(
        WorkUnit $workUnit,
        Carbon $start,
        ?Carbon $end,
        ?WorkUnitCoordinator $except = null,
    ): void {
        $overlap = WorkUnitCoordinator::query()
            ->where('work_unit_id', $workUnit->getKey())
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->whereDate('starts_at', '<=', ($end ?? Carbon::create(9999, 12, 31))->toDateString())
            ->where(function ($query) use ($start): void {
                $query->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $start->toDateString());
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'starts_at' => 'Periode bertumpang tindih dengan koordinator Unit Kerja yang sudah ada.',
            ]);
        }
    }

    /** @return array{Carbon, Carbon|null} */
    private function normalizedPeriod(mixed $startsAt, mixed $endsAt): array
    {
        $start = Carbon::parse($startsAt)->startOfDay();
        $end = filled($endsAt) ? Carbon::parse($endsAt)->startOfDay() : null;

        if ($end?->lt($start)) {
            throw ValidationException::withMessages([
                'ends_at' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            ]);
        }

        return [$start, $end];
    }
}
