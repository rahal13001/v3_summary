<?php

namespace Tests\Feature;

use App\Filament\Resources\ReportResource;
use App\Filament\Resources\ReportResource\RelationManagers\EvaluationsRelationManager;
use App\Models\OrganizationSetting;
use App\Models\Report;
use App\Models\ReportEvaluationRevision;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportEvaluationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_relation_manager_is_registered_but_hidden_when_monev_is_disabled(): void
    {
        config()->set('organization.monev_enabled', false);

        $owner = User::factory()->create();
        $report = Report::factory()->for($owner)->create();
        $this->actingAs($owner);

        $this->assertContains(EvaluationsRelationManager::class, ReportResource::getRelations());
        $this->assertFalse(EvaluationsRelationManager::canViewForRecord($report, 'view'));

        OrganizationSetting::query()->create([
            'name' => 'BPS Kota Kupang',
            'short_name' => 'BPS Kupang',
            'monev_enabled' => true,
        ]);

        $this->assertTrue(EvaluationsRelationManager::canViewForRecord($report, 'view'));
    }

    public function test_relation_manager_visibility_obeys_report_and_current_unit_scope(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'BPS Kota Kupang',
            'short_name' => 'BPS Kupang',
            'monev_enabled' => true,
        ]);
        $owner = User::factory()->create();
        $coordinator = User::factory()->create();
        $unrelated = User::factory()->create();
        $report = Report::factory()->for($owner)->create();
        $unit = WorkUnit::factory()->create();
        $report->workUnits()->attach($unit);
        $unit->coordinatorAssignments()->create([
            'user_id' => $coordinator->id,
            'starts_at' => today()->subDay(),
        ]);

        $this->actingAs($coordinator);
        $this->assertTrue(EvaluationsRelationManager::canViewForRecord($report, 'view'));

        $this->actingAs($unrelated);
        $this->assertFalse(EvaluationsRelationManager::canViewForRecord($report, 'view'));
    }

    public function test_history_view_escapes_untrusted_audit_values(): void
    {
        $revision = new ReportEvaluationRevision([
            'changed_at' => now(),
            'changes' => [
                'keterangan' => [
                    'old' => '<script>alert(1)</script>',
                    'new' => '<img src=x onerror=alert(2)>',
                ],
            ],
        ]);
        $revision->setRelation('editor', User::factory()->make(['name' => '<b>Editor</b>']));

        $html = view('filament.resources.report.evaluation-history', [
            'revisions' => new Collection([$revision]),
        ])->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('<b>Editor</b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_ui_source_uses_service_policy_http_rules_and_has_no_delete_action(): void
    {
        $source = file_get_contents(app_path('Filament/Resources/ReportResource/RelationManagers/EvaluationsRelationManager.php'));

        $this->assertStringContainsString('ReportEvaluationService::class', $source);
        $this->assertStringContainsString('ReportEvaluationPolicy::class', $source);
        $this->assertStringContainsString("rules(['url:http,https'])", $source);
        $this->assertStringNotContainsString('DeleteAction', $source);
    }
}
