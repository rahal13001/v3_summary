<?php

namespace Tests\Feature;

use App\Models\Involvement;
use App\Models\User;
use App\Models\WorkUnit;
use App\Policies\InvolvementPolicy;
use App\Policies\RolePolicy;
use App\Policies\WorkUnitPolicy;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShieldPermissionContractTest extends TestCase
{
    public function test_the_legacy_permission_catalogue_is_preserved(): void
    {
        Filament::setCurrentPanel('admin');

        $permissions = FilamentShield::getEntitiesPermissions();

        $this->assertCount(107, $permissions);
        $this->assertSame([
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
        ], array_values(array_filter(
            $permissions,
            fn (string $permission): bool => str_ends_with($permission, '_role'),
        )));
        $this->assertContains('view_any_user', $permissions);
        $this->assertContains('view_report', $permissions);
        $this->assertContains('view_any_work_unit', $permissions);
        $this->assertContains('view_work_unit', $permissions);
        $this->assertContains('create_work_unit', $permissions);
        $this->assertContains('update_work_unit', $permissions);
        $this->assertContains('delete_work_unit', $permissions);
        $this->assertContains('view_any_involvement', $permissions);
        $this->assertContains('view_involvement', $permissions);
        $this->assertContains('create_involvement', $permissions);
        $this->assertContains('update_involvement', $permissions);
        $this->assertContains('delete_involvement', $permissions);
        $this->assertContains('view_any_organization_setting', $permissions);
        $this->assertContains('view_organization_setting', $permissions);
        $this->assertContains('create_organization_setting', $permissions);
        $this->assertContains('update_organization_setting', $permissions);
        $this->assertNotContains('page_Dashboard', $permissions);
        $this->assertSame([], array_values(array_filter(
            $permissions,
            fn (string $permission): bool => str_contains($permission, ':'),
        )));
    }

    public function test_role_policy_uses_the_permissions_exposed_by_the_catalogue(): void
    {
        $user = new class extends User
        {
            public string $allowedPermission = '';

            public function can($abilities, $arguments = []): bool
            {
                return $abilities === $this->allowedPermission;
            }
        };
        $role = new Role;
        $policy = new RolePolicy;

        $checks = [
            'view_any_role' => fn (): bool => $policy->viewAny($user),
            'view_role' => fn (): bool => $policy->view($user, $role),
            'create_role' => fn (): bool => $policy->create($user),
            'update_role' => fn (): bool => $policy->update($user, $role),
            'delete_role' => fn (): bool => $policy->delete($user, $role),
            'delete_any_role' => fn (): bool => $policy->deleteAny($user),
        ];

        foreach ($checks as $permission => $check) {
            $user->allowedPermission = $permission;

            $this->assertTrue($check(), "RolePolicy did not check [{$permission}].");
        }
    }

    public function test_organization_dimension_policies_use_the_generated_permissions(): void
    {
        $user = new class extends User
        {
            public string $allowedPermission = '';

            public function can($abilities, $arguments = []): bool
            {
                return $abilities === $this->allowedPermission;
            }
        };

        $resources = [
            'work_unit' => [new WorkUnitPolicy, new WorkUnit],
            'involvement' => [new InvolvementPolicy, new Involvement],
        ];

        foreach ($resources as $subject => [$policy, $model]) {
            $checks = [
                "view_any_{$subject}" => fn (): bool => $policy->viewAny($user),
                "view_{$subject}" => fn (): bool => $policy->view($user, $model),
                "create_{$subject}" => fn (): bool => $policy->create($user),
                "update_{$subject}" => fn (): bool => $policy->update($user, $model),
                "delete_{$subject}" => fn (): bool => $policy->delete($user, $model),
                "delete_any_{$subject}" => fn (): bool => $policy->deleteAny($user),
                "force_delete_{$subject}" => fn (): bool => $policy->forceDelete($user, $model),
                "force_delete_any_{$subject}" => fn (): bool => $policy->forceDeleteAny($user),
                "restore_{$subject}" => fn (): bool => $policy->restore($user, $model),
                "restore_any_{$subject}" => fn (): bool => $policy->restoreAny($user),
                "replicate_{$subject}" => fn (): bool => $policy->replicate($user, $model),
                "reorder_{$subject}" => fn (): bool => $policy->reorder($user),
            ];

            foreach ($checks as $permission => $check) {
                $user->allowedPermission = $permission;

                $this->assertTrue($check(), "Policy did not check [{$permission}].");
            }
        }
    }
}
