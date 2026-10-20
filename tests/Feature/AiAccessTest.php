<?php

namespace Tests\Feature;

use App\Models\AiAccessGrant;
use App\Models\Indicator;
use App\Models\Involvement;
use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnit;
use App\Models\WorkUnitCoordinator;
use App\Services\Ai\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AiAccessTest extends TestCase
{
    use RefreshDatabase;

    private function reader(): User
    {
        $user = User::factory()->create(['status' => true]);
        foreach (['use_ai_api', 'view_any_report', 'view_report'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }

    private function token(User $user, array $fields = ['what', 'when', 'tanggal_selesai'], string $scope = 'own_related'): string
    {
        return app(TokenService::class)->issue($user, 'Laptop', ['reports'], ['reports' => $fields], $scope, 30);
    }

    public function test_old_wildcard_tokens_cannot_enter_ai_api(): void
    {
        config(['ai.enabled' => true]);
        $token = $this->reader()->createToken('legacy')->plainTextToken;
        $this->withToken($token)->getJson('/api/ai/v1/capabilities')->assertForbidden();
    }

    public function test_scoped_token_reads_only_granted_columns_and_own_reports(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        $mine = Report::factory()->create(['user_id' => $user->id, 'what' => 'Kegiatan Juli', 'when' => '2026-07-01']);
        Report::factory()->create(['user_id' => User::factory()->create()->id, 'what' => 'Laporan orang lain']);
        $this->withToken($this->token($user))->getJson('/api/ai/v1/reports')
            ->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonPath('data.0.what', 'Kegiatan Juli')
            ->assertJsonMissingPath('data.0.how')->assertJsonMissingPath('data.0.user.email');
    }

    public function test_ai_token_is_rejected_outside_ai_namespace(): void
    {
        $token = $this->token($this->reader());
        $this->withToken($token)->getJson('/api/user')->assertForbidden();
        $this->withToken($token)->postJson('/api/logout')->assertForbidden();
        $this->assertSame(1, AiAccessGrant::count());
    }

    public function test_hidden_fields_cannot_be_used_for_filters_or_sorting(): void
    {
        config(['ai.enabled' => true]);
        $this->withToken($this->token($this->reader()))
            ->getJson('/api/ai/v1/reports?filter[how][contains]=secret')->assertForbidden();
        $this->getJson('/api/ai/v1/reports?sort=how')->assertForbidden();
        $this->getJson('/api/ai/v1/reports?fields=password')->assertUnprocessable();
    }

    public function test_quarter_overlap_and_numeric_binding_do_not_change_business_data(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        $report = Report::factory()->create(['user_id' => $user->id, 'when' => '2026-06-30', 'tanggal_selesai' => '2026-07-02']);
        Report::factory()->create(['user_id' => $user->id, 'when' => '2026-10-01', 'tanggal_selesai' => '2026-10-02']);
        $before = Report::all()->toArray();
        $this->withToken($this->token($user))->getJson('/api/ai/v1/reports?period_start=2026-07-01&period_end=2026-09-30')
            ->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/ai/v1/reports/'.$report->id)->assertOk();
        $this->postJson('/api/ai/v1/reports', ['what' => 'changed'])->assertStatus(405);
        $this->assertSame($before, Report::all()->toArray());
    }

    public function test_revocation_and_account_permission_changes_take_effect(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        $token = $this->token($user);
        $user->revokePermissionTo('use_ai_api');
        $this->withToken($token)->getJson('/api/ai/v1/capabilities')->assertForbidden();
        $user->tokens()->delete();
        $this->assertSame(0, AiAccessGrant::count());
        $this->getJson('/api/ai/v1/capabilities')->assertUnauthorized();
    }

    public function test_session_alone_missing_grant_mixed_abilities_and_expired_tokens_are_rejected(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        $this->actingAs($user)->getJson('/api/ai/v1/capabilities')->assertUnauthorized();
        $this->withToken($user->createToken('missing', ['ai:read'])->plainTextToken)
            ->getJson('/api/ai/v1/capabilities')->assertForbidden();
        $this->withToken($user->createToken('mixed', ['*', 'ai:read'])->plainTextToken)
            ->getJson('/api/user')->assertForbidden();
        $token = $this->token($user);
        $user->tokens()->where('name', 'Laptop')->update(['expires_at' => now()->subMinute()]);
        $this->withToken($token)->getJson('/api/ai/v1/capabilities')->assertUnauthorized();
    }

    public function test_permissive_gate_does_not_override_token_scope_or_explicit_ai_permission(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        Report::factory()->create(['user_id' => User::factory()->create()->id]);
        $token = $this->token($user);
        Gate::before(fn () => true);
        $this->withToken($token)->getJson('/api/ai/v1/reports')->assertOk()->assertJsonPath('meta.total', 0);
        $user->revokePermissionTo('use_ai_api');
        $this->getJson('/api/ai/v1/capabilities')->assertForbidden();
    }

    public function test_followed_reports_are_visible_and_deleted_or_unrelated_reports_are_not(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        $followed = Report::factory()->create(['user_id' => User::factory()->create()->id]);
        $followed->followers()->attach($user);
        $deleted = Report::factory()->create(['user_id' => $user->id]);
        $deleted->delete();
        $this->withToken($this->token($user))->getJson('/api/ai/v1/reports')
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $followed->id);
        $this->getJson('/api/ai/v1/reports/'.$deleted->id)->assertNotFound();
    }

    public function test_relation_permissions_and_fields_apply_to_includes_references_and_filters(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        foreach (['view_any_indicator', 'view_indicator', 'view_any_user', 'view_user'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $report = Report::factory()->create(['user_id' => $user->id]);
        $indicator = Indicator::create(['nomor_iku' => '1', 'nama_iku' => 'Layanan', 'tahun_iku' => '2026', 'status_iku' => 'active']);
        $report->indicators()->attach($indicator);
        $token = app(TokenService::class)->issue($user, 'Relations', ['reports', 'indicators', 'people', 'documentation_metadata'], [
            'reports' => ['what'], 'indicators' => ['id', 'nama_iku'], 'people' => ['id', 'name'], 'documentation_metadata' => ['kinds'],
        ], 'account', 30);
        $this->withToken($token)->getJson('/api/ai/v1/reports?include=indicators,people,documentation_metadata&filter[indicator_id][eq]='.$indicator->id)
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.indicators.0.nama_iku', 'Layanan')
            ->assertJsonMissingPath('data.0.indicators.0.tahun_iku')->assertJsonMissingPath('data.0.people.author.email')
            ->assertJsonMissingPath('data.0.documentation_metadata.st');
        $this->getJson('/api/ai/v1/references/indicators')->assertOk()->assertJsonPath('meta.total', 1);
        $user->revokePermissionTo('view_indicator');
        $this->getJson('/api/ai/v1/reports?include=indicators')->assertForbidden();
        $this->getJson('/api/ai/v1/reports?filter[indicator_id][eq]='.$indicator->id)->assertForbidden();
    }

    public function test_evaluations_respect_coordinator_unit_and_soft_deleted_parent(): void
    {
        config(['ai.enabled' => true, 'organization.monev_enabled' => true]);
        $user = $this->reader();
        $report = Report::factory()->create(['user_id' => User::factory()->create()->id]);
        $mine = WorkUnit::factory()->create();
        $other = WorkUnit::factory()->create();
        WorkUnitCoordinator::create(['user_id' => $user->id, 'work_unit_id' => $mine->id, 'starts_at' => today()->subDay()]);
        ReportEvaluation::factory()->create(['report_id' => $report->id, 'work_unit_id' => $mine->id, 'kendala' => 'Diizinkan']);
        ReportEvaluation::factory()->create(['report_id' => $report->id, 'work_unit_id' => $other->id, 'kendala' => 'Rahasia unit lain']);
        $token = app(TokenService::class)->issue($user, 'Monev', ['reports', 'evaluations'], [
            'reports' => ['what'], 'evaluations' => ['id', 'report_id', 'work_unit_id', 'period', 'kendala'],
        ], 'account', 30);
        $this->withToken($token)->getJson('/api/ai/v1/evaluations')->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.kendala', 'Diizinkan');
        $this->getJson('/api/ai/v1/reports?include=evaluations')->assertOk()->assertJsonCount(1, 'data.0.evaluations');
        $report->delete();
        $this->getJson('/api/ai/v1/evaluations')->assertOk()->assertJsonPath('meta.total', 0);
        config(['organization.monev_enabled' => false]);
        $this->getJson('/api/ai/v1/evaluations')->assertForbidden();
    }

    public function test_literal_search_validation_and_rate_limits(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        Report::factory()->create(['user_id' => $user->id, 'what' => '100% selesai']);
        Report::factory()->create(['user_id' => $user->id, 'what' => '1000 selesai']);
        $this->withToken($this->token($user))->getJson('/api/ai/v1/reports?q=100%25')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/ai/v1/reports?per_page=101')->assertUnprocessable();
        $this->getJson('/api/ai/v1/reports?period_start=2026-09-30&period_end=2026-07-01')->assertUnprocessable();
        $this->getJson('/api/ai/v1/reports?period_start=2026-07-01')->assertUnprocessable();
        $this->getJson('/api/ai/v1/reports?period_end=2026-09-30')->assertUnprocessable();
        $this->getJson('/api/ai/v1/reports?filter[what][eq][]=injection')->assertUnprocessable();
        config(['ai.per_token_per_minute' => 1]);
        $this->getJson('/api/ai/v1/capabilities')->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_ai_bearer_cannot_write_through_existing_web_session(): void
    {
        $user = $this->reader();
        $before = $user->fcm_token;
        $this->actingAs($user)->withToken($this->token($user))->postJson('/webtoken', ['fcm_token' => 'changed'])->assertForbidden();
        $this->assertSame($before, $user->fresh()->fcm_token);
    }

    public function test_older_overlapping_coordinator_assignment_does_not_grant_access(): void
    {
        config(['ai.enabled' => true, 'organization.monev_enabled' => true]);
        $user = $this->reader();
        $report = Report::factory()->create(['user_id' => User::factory()->create()->id]);
        $unit = WorkUnit::factory()->create();
        WorkUnitCoordinator::create(['user_id' => $user->id, 'work_unit_id' => $unit->id, 'starts_at' => today()->subDays(5)]);
        WorkUnitCoordinator::create(['user_id' => User::factory()->create()->id, 'work_unit_id' => $unit->id, 'starts_at' => today()->subDay()]);
        ReportEvaluation::factory()->create(['report_id' => $report->id, 'work_unit_id' => $unit->id]);
        $token = app(TokenService::class)->issue($user, 'Overlap', ['reports', 'evaluations'], ['reports' => ['what'], 'evaluations' => ['id', 'kendala']], 'account', 30);
        $this->withToken($token)->getJson('/api/ai/v1/evaluations')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_nested_followers_are_bounded_and_can_be_read_through_pagination(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        foreach (['view_any_user', 'view_user'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $report = Report::factory()->create(['user_id' => $user->id]);
        $report->followers()->attach(User::factory()->count(27)->create()->modelKeys());
        $token = app(TokenService::class)->issue($user, 'People', ['reports', 'people'], ['reports' => ['what'], 'people' => ['id', 'name']], 'account', 30);
        $this->withToken($token)->getJson('/api/ai/v1/reports/'.$report->id.'?include=people')->assertOk()
            ->assertJsonCount(25, 'data.people.followers')->assertJsonPath('data._relation_meta.followers.total', 27)
            ->assertJsonPath('data._relation_meta.followers.has_more', true);
        $this->getJson('/api/ai/v1/reports/'.$report->id.'/followers?per_page=25&page=2')->assertOk()
            ->assertJsonPath('meta.total', 27)->assertJsonCount(2, 'data');
        $user->revokePermissionTo('view_user');
        $this->getJson('/api/ai/v1/reports/'.$report->id.'?include=people')->assertForbidden();
        $this->getJson('/api/ai/v1/reports/'.$report->id.'/followers')->assertForbidden();
    }

    public function test_boolean_reference_filters_accept_true_and_false(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        foreach (['view_any_involvement', 'view_involvement'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $involvement = Involvement::create(['name' => 'Internal', 'status' => 'active', 'is_lprl_organizer' => true]);
        Report::factory()->create(['user_id' => $user->id, 'involvement_id' => $involvement->id]);
        $token = app(TokenService::class)->issue($user, 'Boolean', ['reports', 'involvements'], [
            'reports' => ['what'], 'involvements' => ['id', 'is_lprl_organizer'],
        ], 'account', 30);
        $this->withToken($token)->getJson('/api/ai/v1/references/involvements?filter[is_lprl_organizer][eq]=true')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/ai/v1/references/involvements?filter[is_lprl_organizer][eq]=false')->assertOk()->assertJsonPath('meta.total', 0);
    }

    public function test_datetime_filter_normalizes_iso_offsets_to_application_timezone(): void
    {
        config(['ai.enabled' => true]);
        $user = $this->reader();
        Report::factory()->create(['user_id' => $user->id, 'created_at' => '2026-07-01 09:00:00']);
        $token = $this->token($user, ['what', 'created_at']);
        $query = http_build_query(['filter' => ['created_at' => ['eq' => '2026-07-01T00:00:00+00:00']]]);
        $this->withToken($token)->getJson('/api/ai/v1/reports?'.$query)->assertOk()->assertJsonPath('meta.total', 1);
    }
}
