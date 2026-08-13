<?php

namespace App\Services;

use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class OrganizationContext
{
    public const CACHE_KEY = 'organization.settings.default';

    public function setting(): ?OrganizationSetting
    {
        if (! Schema::hasTable('organization_settings')) {
            return null;
        }

        $attributes = Cache::get(self::CACHE_KEY);

        if ($attributes !== null && ! is_array($attributes)) {
            Cache::forget(self::CACHE_KEY);
            $attributes = null;
        }

        $attributes ??= Cache::rememberForever(
            self::CACHE_KEY,
            fn (): ?array => OrganizationSetting::query()
                ->where('key', OrganizationSetting::DEFAULT_KEY)
                ->first()
                ?->getAttributes(),
        );

        return is_array($attributes)
            ? (new OrganizationSetting)->newFromBuilder($attributes)
            : null;
    }

    public function name(): string
    {
        return $this->setting()?->name ?: (string) config('organization.name', 'LPRL Sorong');
    }

    public function shortName(): string
    {
        return $this->setting()?->short_name ?: (string) config('organization.short_name', 'LPRL Sorong');
    }

    public function appName(): string
    {
        return $this->setting()?->app_name ?: (string) config('organization.app_name', 'Summary');
    }

    public function logoPath(): ?string
    {
        return $this->setting()?->logo_path ?: config('organization.logo_path');
    }

    public function faviconPath(): ?string
    {
        return $this->setting()?->favicon_path ?: config('organization.favicon_path');
    }

    public function logoUrl(): ?string
    {
        return $this->publicAssetUrl($this->logoPath());
    }

    public function faviconUrl(): ?string
    {
        return $this->publicAssetUrl($this->faviconPath());
    }

    public function address(): ?string
    {
        return $this->setting()?->address ?: config('organization.address');
    }

    public function organizerName(): string
    {
        return $this->setting()?->organizer_name
            ?: ((string) config('organization.organizer_name') ?: $this->shortName());
    }

    public function organizerInputMode(): string
    {
        $mode = $this->setting()?->organizer_input_mode
            ?: (string) config('organization.organizer_input_mode', OrganizationSetting::ORGANIZER_MODE_LOCKED);

        return array_key_exists($mode, OrganizationSetting::organizerInputModeOptions())
            ? $mode
            : OrganizationSetting::ORGANIZER_MODE_LOCKED;
    }

    public function monevEnabled(): bool
    {
        return $this->setting()?->monev_enabled ?? (bool) config('organization.monev_enabled', false);
    }

    private function publicAssetUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        try {
            return Storage::disk('public')->exists($path)
                ? Storage::disk('public')->url($path)
                : null;
        } catch (Throwable) {
            return null;
        }
    }
}
