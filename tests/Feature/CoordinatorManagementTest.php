<?php

namespace Tests\Feature;

use App\Filament\Resources\WorkUnitResource\RelationManagers\CoordinatorAssignmentsRelationManager;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\CoordinatorAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CoordinatorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinator_schema_and_signature_profile_are_available(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'coordinator_signature_path'));
        $this->assertTrue(Schema::hasColumns('work_unit_coordinators', [
            'id',
            'work_unit_id',
            'user_id',
            'starts_at',
            'ends_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_a_user_can_coordinate_multiple_work_units_without_overlapping_one_unit(): void
    {
        $user = User::factory()->create();
        $firstUnit = $this->workUnit('Unit A');
        $secondUnit = $this->workUnit('Unit B');
        $service = app(CoordinatorAssignmentService::class);

        $service->assign($firstUnit, $user, '2026-01-01');
        $service->assign($secondUnit, $user, '2026-01-01');

        $this->assertCount(2, $user->coordinatorAssignments);

        $this->expectException(ValidationException::class);

        $service->assign($firstUnit, User::factory()->create(), '2026-02-01');
    }

    public function test_non_overlapping_replacement_preserves_assignment_history(): void
    {
        $unit = $this->workUnit('Unit A');
        $service = app(CoordinatorAssignmentService::class);
        $first = $service->assign($unit, User::factory()->create(), '2026-01-01', '2026-06-30');
        $second = $service->assign($unit, User::factory()->create(), '2026-07-01');

        $this->assertDatabaseCount('work_unit_coordinators', 2);
        $this->assertTrue($unit->coordinatorAt('2026-06-01')->is($first->user));
        $this->assertTrue($unit->coordinatorAt('2026-08-01')->is($second->user));
    }

    public function test_signature_replacement_deletes_old_file_only_after_commit(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('coordinator-signatures/old.png', 'old');
        Storage::disk('local')->put('coordinator-signatures/new.png', 'new');

        $user = User::factory()->create([
            'coordinator_signature_path' => 'coordinator-signatures/old.png',
        ]);
        $user->update([
            'coordinator_signature_path' => 'coordinator-signatures/new.png',
        ]);

        Storage::disk('local')->assertMissing('coordinator-signatures/old.png');
        Storage::disk('local')->assertExists('coordinator-signatures/new.png');
    }

    public function test_signature_old_file_survives_a_rolled_back_update(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('coordinator-signatures/old.png', 'old');

        $user = User::factory()->create([
            'coordinator_signature_path' => 'coordinator-signatures/old.png',
        ]);

        try {
            DB::transaction(function () use ($user): void {
                $user->update([
                    'coordinator_signature_path' => 'coordinator-signatures/new.png',
                ]);

                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
            // Expected rollback.
        }

        Storage::disk('local')->assertExists('coordinator-signatures/old.png');
        $this->assertSame(
            'coordinator-signatures/old.png',
            $user->fresh()->coordinator_signature_path,
        );
    }

    public function test_coordinator_relation_manager_requires_work_unit_update_permission(): void
    {
        $unit = $this->workUnit('Unit A');
        $allowed = User::factory()->create();
        $denied = User::factory()->create();
        $allowed->givePermissionTo(Permission::findOrCreate('update_work_unit'));

        $this->actingAs($allowed);
        $this->assertTrue(CoordinatorAssignmentsRelationManager::canViewForRecord($unit, 'edit'));

        $this->actingAs($denied);
        $this->assertFalse(CoordinatorAssignmentsRelationManager::canViewForRecord($unit, 'edit'));
    }

    public function test_eager_loaded_current_coordinator_profiles_have_bounded_queries(): void
    {
        $service = app(CoordinatorAssignmentService::class);

        foreach (range(1, 4) as $number) {
            $service->assign(
                $this->workUnit("Unit {$number}"),
                User::factory()->create([
                    'coordinator_signature_path' => "coordinator-signatures/{$number}.png",
                ]),
                now()->subMonth(),
            );
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $profiles = WorkUnit::query()
            ->with('currentCoordinatorAssignment.user')
            ->get()
            ->map(fn (WorkUnit $unit): array => [
                $unit->currentCoordinatorAssignment?->user?->name,
                $unit->currentCoordinatorAssignment?->user?->coordinator_signature_path,
            ]);

        $this->assertCount(4, $profiles);
        $this->assertLessThanOrEqual(3, count(DB::getQueryLog()));
    }

    public function test_coordinator_ui_uses_private_validated_images_and_keeps_history_read_only(): void
    {
        $userResource = file_get_contents(app_path('Filament/Resources/UserResource.php'));
        $profilePage = file_get_contents(app_path('Filament/Pages/Auth/EditProfile.php'));
        $relationManager = file_get_contents(app_path('Filament/Resources/WorkUnitResource/RelationManagers/CoordinatorAssignmentsRelationManager.php'));

        foreach ([$userResource, $profilePage] as $source) {
            $this->assertStringContainsString("FileUpload::make('coordinator_signature_path')", $source);
            $this->assertStringContainsString("->disk('local')", $source);
            $this->assertStringContainsString("'image/jpeg', 'image/png'", $source);
        }

        $this->assertStringContainsString('CoordinatorAssignmentService::class', $relationManager);
        $this->assertStringNotContainsString('DeleteAction::make()', $relationManager);
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
