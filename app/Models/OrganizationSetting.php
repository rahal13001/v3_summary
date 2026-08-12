<?php

namespace App\Models;

use App\Services\OrganizationContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class OrganizationSetting extends Model
{
    use HasFactory;

    public const DEFAULT_KEY = 'default';

    protected $fillable = [
        'name',
        'short_name',
        'logo_path',
        'address',
        'monev_enabled',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrganizationSetting $setting): void {
            $setting->key = self::DEFAULT_KEY;
        });

        static::saved(fn (): bool => Cache::forget(OrganizationContext::CACHE_KEY));
        static::deleted(fn (): bool => Cache::forget(OrganizationContext::CACHE_KEY));
    }

    protected function casts(): array
    {
        return [
            'monev_enabled' => 'boolean',
        ];
    }
}
