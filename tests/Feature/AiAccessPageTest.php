<?php

namespace Tests\Feature;

use App\Filament\Pages\AiAccess;
use App\Models\AiAccessGrant;
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
}
