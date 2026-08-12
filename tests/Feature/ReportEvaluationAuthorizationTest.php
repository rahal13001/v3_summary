<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use App\Models\WorkUnit;
use App\Policies\ReportEvaluationPolicy;
use App\Services\CoordinatorAssignmentService;
use App\Services\ReportEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportEvaluationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_follower_can_manage_their_report_evaluation(): void
    {
        [$report, $firstUnit, , $owner] = $this->context();
        $follower = User::factory()->create();
        $report->followers()->attach($follower);
        $evaluation = app(ReportEvaluationService::class)->create($report, $firstUnit, '2026-08-01', [], $owner);
        $policy = new ReportEvaluationPolicy;

        $this->assertTrue($policy->update($owner, $evaluation));
        $this->assertTrue($policy->update($follower, $evaluation));
    }

    public function test_coordinator_can_only_manage_the_current_unit(): void
    {
        [$report, $firstUnit, $secondUnit, $owner] = $this->context();
        $coordinator = User::factory()->create();
        app(CoordinatorAssignmentService::class)->assign($firstUnit, $coordinator, now()->subMonth());
        $firstEvaluation = app(ReportEvaluationService::class)->create($report, $firstUnit, '2026-08-01', [], $owner);
        $secondEvaluation = app(ReportEvaluationService::class)->create($report, $secondUnit, '2026-08-01', [], $owner);
        $policy = new ReportEvaluationPolicy;

        $this->assertTrue($policy->update($coordinator, $firstEvaluation));
        $this->assertFalse($policy->update($coordinator, $secondEvaluation));
    }

    public function test_unrelated_user_is_denied_but_admin_and_dedicated_ability_are_global(): void
    {
        [$report, $unit, , $owner] = $this->context();
        $evaluation = app(ReportEvaluationService::class)->create($report, $unit, '2026-08-01', [], $owner);
        $unrelated = User::factory()->create();
        $admin = User::factory()->create();
        $leader = User::factory()->create();
        Role::findOrCreate('admin')->givePermissionTo([]);
        $admin->assignRole('admin');
        $leader->givePermissionTo(Permission::findOrCreate(ReportEvaluationPolicy::MANAGE_ALL));
        $policy = new ReportEvaluationPolicy;

        $this->assertFalse($policy->update($unrelated, $evaluation));
        $this->assertTrue($policy->update($admin, $evaluation));
        $this->assertTrue($policy->update($leader, $evaluation));
        $this->assertTrue($policy->viewHistory($leader, $evaluation));
    }

    public function test_export_requires_dedicated_ability_and_unit_context_unless_global(): void
    {
        [, $firstUnit, $secondUnit] = $this->context();
        $coordinator = User::factory()->create();
        $leader = User::factory()->create();
        app(CoordinatorAssignmentService::class)->assign($firstUnit, $coordinator, now()->subMonth());
        $exportPermission = Permission::findOrCreate(ReportEvaluationPolicy::EXPORT);
        $coordinator->givePermissionTo($exportPermission);
        $leader->givePermissionTo([
            $exportPermission,
            Permission::findOrCreate(ReportEvaluationPolicy::MANAGE_ALL),
        ]);
        $policy = new ReportEvaluationPolicy;

        $this->assertTrue($policy->export($coordinator, $firstUnit));
        $this->assertFalse($policy->export($coordinator, $secondUnit));
        $this->assertTrue($policy->export($leader, $secondUnit));
        $this->assertFalse($policy->export(User::factory()->create(), $firstUnit));
    }

    public function test_report_wide_access_excludes_coordinator_only_access(): void
    {
        $owner = User::factory()->create();
        $follower = User::factory()->create();
        $coordinator = User::factory()->create();
        [$report] = $this->context();
        $report->user_id = $owner->id;
        $report->save();
        $report->followers()->attach($follower);
        $unit = WorkUnit::factory()->create();
        $report->workUnits()->attach($unit);
        $unit->coordinatorAssignments()->create([
            'user_id' => $coordinator->id,
            'starts_at' => today()->subDay(),
        ]);
        $policy = app(ReportEvaluationPolicy::class);

        $this->assertTrue($policy->hasReportWideAccess($owner, $report));
        $this->assertTrue($policy->hasReportWideAccess($follower, $report));
        $this->assertFalse($policy->hasReportWideAccess($coordinator, $report));
        $this->assertTrue($policy->canManageReport($coordinator, $report));
    }

    /** @return array{Report, WorkUnit, WorkUnit, User} */
    private function context(): array
    {
        $owner = User::factory()->create();
        $report = Report::query()->create([
            'user_id' => $owner->id,
            'slug' => fake()->unique()->slug(),
            'no_st' => 'ST-001',
            'what' => 'Kegiatan uji',
            'why' => 'Pengujian',
            'when' => '2026-08-01',
            'tanggal_selesai' => '2026-08-02',
            'where' => 'Kupang',
            'who' => 'Pegawai',
            'how' => 'Pelaksanaan',
            'penyelenggara' => 'Organisasi',
            'total_peserta' => '10',
            'total_wanita' => '50',
            'kode' => 'signature',
        ]);
        $firstUnit = $this->workUnit('Unit A');
        $secondUnit = $this->workUnit('Unit B');
        $report->workUnits()->attach([$firstUnit->id, $secondUnit->id]);

        return [$report, $firstUnit, $secondUnit, $owner];
    }

    private function workUnit(string $name): WorkUnit
    {
        return WorkUnit::query()->create([
            'name' => $name,
            'status' => WorkUnit::STATUS_ACTIVE,
            'unit' => WorkUnit::UNIT_SERVICE_UNIT,
        ]);
    }
}
