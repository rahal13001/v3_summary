<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\RolePolicy;
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

        $this->assertCount(71, $permissions);
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
}
