<?php

namespace Tests\Feature;

use App\Models\Involvement;
use App\Models\OrganizationSetting;
use App\Services\OrganizationContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_setting_schema_is_additive_and_typed(): void
    {
        $this->assertTrue(Schema::hasColumns('organization_settings', [
            'id',
            'key',
            'name',
            'short_name',
            'logo_path',
            'address',
            'monev_enabled',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_empty_setting_uses_safe_sorong_compatible_defaults(): void
    {
        $organization = app(OrganizationContext::class);

        $this->assertSame('LPRL Sorong', $organization->name());
        $this->assertSame('LPRL Sorong', $organization->shortName());
        $this->assertFalse($organization->monevEnabled());
    }

    public function test_saved_setting_replaces_defaults_and_invalidates_cache(): void
    {
        Cache::forever(OrganizationContext::CACHE_KEY, null);

        OrganizationSetting::query()->create([
            'name' => 'Loka Pengelolaan Sumberdaya Pesisir dan Laut Kupang',
            'short_name' => 'LPSPL Kupang',
            'address' => 'Kupang',
            'monev_enabled' => true,
        ]);

        $organization = app(OrganizationContext::class);

        $this->assertSame('LPSPL Kupang', $organization->shortName());
        $this->assertTrue($organization->monevEnabled());
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

    public function test_order_email_uses_deployment_identity_and_url(): void
    {
        $source = file_get_contents(resource_path('views/emails/order_reminder.blade.php'));

        $this->assertStringContainsString('OrganizationContext::class)->shortName()', $source);
        $this->assertStringContainsString("url('/disposisi/'.\$order->order_slug)", $source);
        $this->assertStringNotContainsString('summary.timurbersinar.com', $source);
        $this->assertStringNotContainsString('LPSPL Sorong', $source);
    }
}
