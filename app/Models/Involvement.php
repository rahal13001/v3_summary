<?php

namespace App\Models;

use App\Services\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Involvement extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'status',
        'is_lprl_organizer',
    ];

    protected function casts(): array
    {
        return [
            'is_lprl_organizer' => 'boolean',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
        ];
    }

    public function scopeSelectableForReport(Builder $query, ?int $selectedId): Builder
    {
        return $query->where(function (Builder $query) use ($selectedId): void {
            $query->where('status', self::STATUS_ACTIVE);

            if ($selectedId !== null) {
                $query->orWhere($query->getModel()->getQualifiedKeyName(), $selectedId);
            }
        });
    }

    public function organizerName(): ?string
    {
        if (! $this->is_lprl_organizer) {
            return null;
        }

        $organization = app(OrganizationContext::class);

        return $organization->organizerInputMode() === OrganizationSetting::ORGANIZER_MODE_MANUAL
            ? null
            : $organization->organizerName();
    }

    public function organizerInputIsLocked(): bool
    {
        return $this->is_lprl_organizer
            && app(OrganizationContext::class)->organizerInputMode() === OrganizationSetting::ORGANIZER_MODE_LOCKED;
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
