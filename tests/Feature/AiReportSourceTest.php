<?php

namespace Tests\Feature;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use App\Models\User;
use App\Services\Ai\TokenService;
use Filament\Auth\Http\Responses\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiReportSourceTest extends TestCase
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

    public function test_api_sources_use_the_existing_browser_page_without_api_or_numeric_record_path(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        $report = Report::factory()->for($user)->create();
        $token = app(TokenService::class)->issue($user, 'Sources', ['reports'], ['reports' => ['what']], 'own_related', 30);
        $expected = ReportResource::getUrl('view', ['record' => $report], panel: 'admin');

        $this->assertStringEndsWith('/laporan-5w1h/'.$report->slug, $expected);
        $this->assertStringNotContainsString('/api/', $expected);
        $this->withToken($token)->getJson('/api/ai/v1/reports')
            ->assertOk()->assertJsonPath('data.0.source.url', $expected);
        $this->getJson('/api/ai/v1/reports/'.$report->id)
            ->assertOk()->assertJsonPath('data.source.url', $expected);
    }

    public function test_guest_source_link_requires_login_and_preserves_the_intended_report(): void
    {
        $report = Report::factory()->for($this->reader())->create();
        $url = ReportResource::getUrl('view', ['record' => $report], panel: 'admin');

        $this->get($url)->assertRedirect(route('filament.admin.auth.login'))
            ->assertSessionHas('url.intended', $url);

        Filament::setCurrentPanel('admin');
        $this->actingAs($report->user);
        $response = (new LoginResponse)->toResponse(request());
        $this->assertSame($url, $response->getTargetUrl());
    }

    public function test_logged_in_reader_can_view_the_source_report(): void
    {
        $user = $this->reader();
        $report = Report::factory()->for($user)->create(['what' => 'Sumber kegiatan terverifikasi']);

        $this->actingAs($user)->get(ReportResource::getUrl('view', ['record' => $report], panel: 'admin'))
            ->assertOk()->assertSee('Sumber kegiatan terverifikasi');
    }

    public function test_browser_source_link_does_not_bypass_report_permission_or_account_status(): void
    {
        $user = $this->reader();
        $report = Report::factory()->for($user)->create();
        $url = ReportResource::getUrl('view', ['record' => $report], panel: 'admin');
        $user->revokePermissionTo('view_report');
        $this->actingAs($user)->get($url)->assertForbidden();
        $user->givePermissionTo('view_report');
        $user->update(['status' => false]);
        $this->actingAs($user)->get($url)->assertForbidden();
    }

    public function test_ai_bearer_token_cannot_replace_browser_login(): void
    {
        $user = $this->reader();
        $report = Report::factory()->for($user)->create();
        $token = app(TokenService::class)->issue($user, 'Sources', ['reports'], ['reports' => ['what']], 'own_related', 30);

        $this->withToken($token)->get(ReportResource::getUrl('view', ['record' => $report], panel: 'admin'))
            ->assertForbidden();
    }
}
