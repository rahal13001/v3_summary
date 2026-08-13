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

    public const ORGANIZER_MODE_LOCKED = 'locked';

    public const ORGANIZER_MODE_EDITABLE = 'editable';

    public const ORGANIZER_MODE_MANUAL = 'manual';

    protected $fillable = [
        'app_name',
        'name',
        'short_name',
        'logo_path',
        'favicon_path',
        'address',
        'organizer_name',
        'organizer_input_mode',
        'monev_enabled',
    ];

    public static function organizerInputModeOptions(): array
    {
        return [
            self::ORGANIZER_MODE_LOCKED => 'Otomatis dan dikunci',
            self::ORGANIZER_MODE_EDITABLE => 'Otomatis, dapat diubah',
            self::ORGANIZER_MODE_MANUAL => 'Diisi manual',
        ];
    }

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
