<?php

namespace Tests\Feature;

use App\Filament\Pages\AiAccess;
use App\Models\AiAccessGrant;
use App\Models\Indicator;
use App\Models\Report;
use App\Models\User;
use App\Services\Ai\TokenService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiAccessPageTest extends TestCase
{
    use RefreshDatabase;

    private function reader(): User
    {
        $user = User::factory()->create(['status' => true]);
        $user->assignRole(Role::findOrCreate('writer', 'web'));
        foreach (['use_ai_api', 'view_any_report', 'view_report'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    public function test_human_can_issue_once_display_and_revoke_own_token(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->reader());
        $page = Livewire::test(AiAccess::class)->assertOk()->set('tokenName', 'Laptop')->call('issue')->assertHasNoErrors();
        $this->assertIsString($page->get('plainToken'));
        $grant = AiAccessGrant::firstOrFail();
        $this->assertSame('own_related', $grant->report_scope);
        $page->call('dismissToken')->assertSet('plainToken', null)->call('revoke', $grant->personal_access_token_id)->assertHasNoErrors();
        $this->assertSame(0, AiAccessGrant::count());
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        Filament::setCurrentPanel('admin');
        $owner = $this->reader();
        app(TokenService::class)->issue($owner, 'Private', ['reports'], ['reports' => ['what']], 'own_related', 30);
        $id = AiAccessGrant::firstOrFail()->personal_access_token_id;
        $this->actingAs($this->reader());
        Livewire::test(AiAccess::class)->call('revoke', $id)->assertNotFound();
        $this->assertSame(1, AiAccessGrant::count());
    }

    public function test_unprivileged_user_cannot_open_page_or_issue_unknown_fields(): void
    {
        Filament::setCurrentPanel('admin');
        $user = $this->reader();
        $this->actingAs($user);
        Livewire::test(AiAccess::class)->set('tokenName', 'Invalid')->set('fields.reports', ['password'])->call('issue')->assertHasErrors('fields');
        $user->revokePermissionTo('use_ai_api');
        Livewire::test(AiAccess::class)->assertForbidden();
    }

    public function test_every_available_dataset_starts_with_an_array_for_checkbox_binding(): void
    {
        Filament::setCurrentPanel('admin');
        config(['organization.monev_enabled' => true, 'ai.enabled' => true]);
        $user = $this->reader();
        foreach (['indicator', 'team', 'work_unit', 'involvement', 'user'] as $resource) {
            foreach (['view_any_', 'view_'] as $prefix) {
                $user->givePermissionTo(Permission::findOrCreate($prefix.$resource, 'web'));
            }
        }
        $this->actingAs($user);
        $page = Livewire::test(AiAccess::class)->assertOk();
        $available = $page->instance()->availableDatasets();
        $this->assertArrayHasKey('indicators', $available);
        $this->assertCount(8, $available);
        foreach (array_keys($available) as $dataset) {
            $page->assertSet('fields.'.$dataset, $dataset === 'reports' ? ['what', 'when', 'tanggal_selesai'] : []);
        }
        $page->assertSet('datasets', ['reports']);

        $fields = array_map(fn (array $columns): array => array_keys($columns), $available);
        $page->set('tokenName', 'Semua relasi')->set('datasets', array_keys($available))
            ->set('fields', $fields)->call('issue')->assertHasNoErrors();
        $grant = AiAccessGrant::firstOrFail();
        $this->assertSame(array_keys($available), $grant->datasets);
        $this->assertSame($fields, $grant->fields);
        $report = Report::factory()->for($user)->create();
        $indicator = Indicator::create(['nomor_iku' => '1', 'nama_iku' => 'Layanan', 'tahun_iku' => '2026', 'status_iku' => 'active']);
        $report->indicators()->attach($indicator);
        $this->withToken($page->get('plainToken'))->getJson('/api/ai/v1/reports/'.$report->id.'?include=indicators')
            ->assertOk()->assertJsonPath('data.indicators.0.id', $indicator->id)
            ->assertJsonPath('data.indicators.0.nama_iku', 'Layanan');
    }

    public function test_checkbox_values_cannot_bypass_field_or_dataset_permissions(): void
    {
        Filament::setCurrentPanel('admin');
        $user = $this->reader();
        foreach (['view_any_indicator', 'view_indicator'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $this->actingAs($user);
        Livewire::test(AiAccess::class)->set('tokenName', 'Invalid checkbox')
            ->set('datasets', ['reports', 'indicators'])->set('fields.indicators', true)
            ->call('issue')->assertHasErrors('fields');
        Livewire::test(AiAccess::class)->set('tokenName', 'Invalid field')
            ->set('datasets', ['reports', 'indicators'])->set('fields.indicators', ['password'])
            ->call('issue')->assertHasErrors('fields');
        $page = Livewire::test(AiAccess::class)->set('tokenName', 'Revoked permission')
            ->set('datasets', ['reports', 'indicators'])->set('fields.indicators', ['id']);
        $user->revokePermissionTo('view_indicator');
        $page->call('issue')->assertHasErrors('fields');
        $this->assertSame(0, AiAccessGrant::count());
    }
}
