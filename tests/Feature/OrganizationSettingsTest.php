<?php

namespace Tests\Feature;

use App\Models\Involvement;
use App\Models\OrganizationSetting;
use App\Services\OrganizationContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_setting_schema_is_additive_and_typed(): void
    {
        $this->assertTrue(Schema::hasColumns('organization_settings', [
            'id',
            'key',
            'app_name',
            'name',
            'short_name',
            'logo_path',
            'favicon_path',
            'address',
            'organizer_name',
            'organizer_input_mode',
            'monev_enabled',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_empty_setting_uses_safe_sorong_compatible_defaults(): void
    {
        config()->set('organization.app_name', 'Summary');
        config()->set('organization.monev_enabled', false);

        $organization = app(OrganizationContext::class);

        $this->assertSame('Summary', $organization->appName());
        $this->assertSame('LPRL Sorong', $organization->name());
        $this->assertSame('LPRL Sorong', $organization->shortName());
        $this->assertSame('LPRL Sorong', $organization->organizerName());
        $this->assertSame(OrganizationSetting::ORGANIZER_MODE_LOCKED, $organization->organizerInputMode());
        $this->assertFalse($organization->monevEnabled());
    }

    public function test_saved_setting_replaces_defaults_and_invalidates_cache(): void
    {
        Cache::forever(OrganizationContext::CACHE_KEY, null);

        OrganizationSetting::query()->create([
            'app_name' => 'Teripang',
            'name' => 'Loka Pengelolaan Sumberdaya Pesisir dan Laut Kupang',
            'short_name' => 'LPSPL Kupang',
            'organizer_name' => 'BPK Kupang',
            'organizer_input_mode' => OrganizationSetting::ORGANIZER_MODE_EDITABLE,
            'address' => 'Kupang',
            'monev_enabled' => true,
        ]);

        $organization = app(OrganizationContext::class);

        $this->assertSame('Teripang', $organization->appName());
        $this->assertSame('LPSPL Kupang', $organization->shortName());
        $this->assertSame('BPK Kupang', $organization->organizerName());
        $this->assertSame(OrganizationSetting::ORGANIZER_MODE_EDITABLE, $organization->organizerInputMode());
        $this->assertTrue($organization->monevEnabled());
    }

    public function test_brand_asset_urls_require_existing_public_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('organisasi/logo.png', $this->png());
        Storage::disk('public')->put('organisasi/favicon.webp', 'webp');

        OrganizationSetting::query()->create([
            'name' => 'Balai Pengelolaan Kelautan Kupang',
            'short_name' => 'BPK Kupang',
            'logo_path' => 'organisasi/logo.png',
            'favicon_path' => 'organisasi/favicon.webp',
        ]);

        $organization = app(OrganizationContext::class);

        $this->assertSame(Storage::disk('public')->url('organisasi/logo.png'), $organization->logoUrl());
        $this->assertSame(Storage::disk('public')->url('organisasi/favicon.webp'), $organization->faviconUrl());

        Storage::disk('public')->delete('organisasi/logo.png');
        Storage::disk('public')->delete('organisasi/favicon.webp');

        $this->assertNull($organization->logoUrl());
        $this->assertNull($organization->faviconUrl());
    }

    public function test_organization_context_recovers_from_a_legacy_serialized_model_cache(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'Loka Pengelolaan Sumberdaya Pesisir dan Laut Kupang',
            'short_name' => 'LPSPL Kupang',
            'monev_enabled' => true,
        ]);

        Cache::forever(
            OrganizationContext::CACHE_KEY,
            unserialize('O:25:"LegacyOrganizationSetting":0:{}'),
        );

        $organization = app(OrganizationContext::class);

        $this->assertSame('LPSPL Kupang', $organization->shortName());
        $this->assertTrue($organization->monevEnabled());
        $this->assertIsArray(Cache::get(OrganizationContext::CACHE_KEY));
    }

    public function test_only_one_default_organization_setting_can_exist(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'Organisasi A',
            'short_name' => 'A',
        ]);

        $this->expectException(QueryException::class);

        OrganizationSetting::query()->create([
            'name' => 'Organisasi B',
            'short_name' => 'B',
        ]);
    }

    public function test_internal_involvement_uses_configured_organization_without_name_matching(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'Loka Pengelolaan Sumberdaya Pesisir dan Laut Kupang',
            'short_name' => 'LPSPL Kupang',
        ]);

        $internal = new Involvement([
            'name' => 'Nama bebas yang bukan Penyelenggara',
            'is_lprl_organizer' => true,
        ]);
        $external = new Involvement([
            'name' => 'Penyelenggara',
            'is_lprl_organizer' => false,
        ]);

        $this->assertSame('LPSPL Kupang', $internal->organizerName());
        $this->assertNull($external->organizerName());
    }

    public function test_internal_involvement_obeys_configured_organizer_input_mode(): void
    {
        $setting = OrganizationSetting::query()->create([
            'name' => 'Balai Pengelolaan Kelautan Kupang',
            'short_name' => 'BPK Kupang',
            'organizer_name' => 'Balai PK Kupang',
            'organizer_input_mode' => OrganizationSetting::ORGANIZER_MODE_EDITABLE,
        ]);
        $internal = new Involvement([
            'name' => 'Internal',
            'is_lprl_organizer' => true,
        ]);

        $this->assertSame('Balai PK Kupang', $internal->organizerName());
        $this->assertFalse($internal->organizerInputIsLocked());

        $setting->update(['organizer_input_mode' => OrganizationSetting::ORGANIZER_MODE_MANUAL]);

        $this->assertNull($internal->organizerName());
        $this->assertFalse($internal->organizerInputIsLocked());

        $setting->update(['organizer_input_mode' => OrganizationSetting::ORGANIZER_MODE_LOCKED]);

        $this->assertSame('Balai PK Kupang', $internal->organizerName());
        $this->assertTrue($internal->organizerInputIsLocked());
    }

    public function test_unknown_organizer_input_mode_falls_back_to_locked(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'Organisasi Uji',
            'short_name' => 'Organisasi Uji',
            'organizer_input_mode' => 'unsupported-mode',
        ]);

        $this->assertSame(
            OrganizationSetting::ORGANIZER_MODE_LOCKED,
            app(OrganizationContext::class)->organizerInputMode(),
        );
    }

    public function test_organization_setting_form_exposes_branding_and_organizer_policy(): void
    {
        $source = file_get_contents(app_path('Filament/Resources/OrganizationSettingResource.php'));

        foreach (['app_name', 'logo_path', 'favicon_path', 'organizer_name', 'organizer_input_mode'] as $field) {
            $this->assertStringContainsString("make('{$field}')", $source);
        }

        $this->assertStringContainsString('OrganizationSetting::organizerInputModeOptions()', $source);
    }

    public function test_order_email_uses_deployment_identity_and_url(): void
    {
        $source = file_get_contents(resource_path('views/emails/order_reminder.blade.php'));

        $this->assertStringContainsString('$organization->appName()', $source);
        $this->assertStringContainsString('$organization->logoUrl()', $source);
        $this->assertStringContainsString("url('/disposisi/'.\$order->order_slug)", $source);
        $this->assertStringNotContainsString('summary.timurbersinar.com', $source);
        $this->assertStringNotContainsString('LPSPL Sorong', $source);
    }

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }
}
