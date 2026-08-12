<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class OrganizationContext
{
    public const CACHE_KEY = 'organization.settings.default';

    public function setting(): ?OrganizationSetting
    {
        if (! Schema::hasTable('organization_settings')) {
            return null;
        }

        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): ?OrganizationSetting => OrganizationSetting::query()
                ->where('key', OrganizationSetting::DEFAULT_KEY)
                ->first(),
        );
    }

    public function name(): string
    {
        return $this->setting()?->name ?: config('organization.name');
    }

    public function shortName(): string
    {
        return $this->setting()?->short_name ?: config('organization.short_name');
    }

    public function logoPath(): ?string
    {
        return $this->setting()?->logo_path ?: config('organization.logo_path');
    }

    public function address(): ?string
    {
        return $this->setting()?->address ?: config('organization.address');
    }

    public function monevEnabled(): bool
    {
        return $this->setting()?->monev_enabled ?? (bool) config('organization.monev_enabled', false);
    }
}
