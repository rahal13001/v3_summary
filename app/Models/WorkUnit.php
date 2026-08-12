<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class WorkUnit extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const UNIT_SERVICE_UNIT = 'satuan_pelayanan';

    public const UNIT_WORK_AREA = 'wilayah_kerja';

    public const UNIT_SERVICE_OUTLET = 'gerai_pelayanan';

    protected $fillable = [
        'name',
        'status',
        'unit',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
        ];
    }

    public static function unitOptions(): array
    {
        return [
            self::UNIT_SERVICE_UNIT => 'Satuan Pelayanan',
            self::UNIT_WORK_AREA => 'Wilayah Kerja',
            self::UNIT_SERVICE_OUTLET => 'Gerai Pelayanan',
        ];
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(
            Report::class,
            'report_work_unit',
            'work_unit_id',
            'report_id',
        );
    }

    public function coordinatorAssignments(): HasMany
    {
        return $this->hasMany(WorkUnitCoordinator::class);
    }

    public function currentCoordinatorAssignment(): HasOne
    {
        $today = today()->toDateString();

        return $this->hasOne(WorkUnitCoordinator::class)
            ->whereDate('starts_at', '<=', $today)
            ->where(fn ($query) => $query
                ->whereNull('ends_at')
                ->orWhereDate('ends_at', '>=', $today))
            ->orderByDesc('starts_at')
            ->orderByDesc('id');
    }

    public function coordinatorAt(mixed $date): ?User
    {
        $day = Carbon::parse($date)->toDateString();

        return $this->coordinatorAssignments()
            ->with('user')
            ->whereDate('starts_at', '<=', $day)
            ->where(fn ($query) => $query
                ->whereNull('ends_at')
                ->orWhereDate('ends_at', '>=', $day))
            ->latest('starts_at')
            ->first()
            ?->user;
    }
}
