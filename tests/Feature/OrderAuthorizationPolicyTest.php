<?php

namespace Tests\Feature;

use App\Models\Executor;
use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Tests\TestCase;

class OrderAuthorizationPolicyTest extends TestCase
{
    public function test_unrelated_user_cannot_view_or_modify_an_order_with_generic_permissions(): void
    {
        $user = $this->user(7, ['view_order', 'update_order', 'delete_order']);
        $order = $this->order(42, [8]);
        $policy = new OrderPolicy;

        $this->assertFalse($policy->view($user, $order));
        $this->assertFalse($policy->update($user, $order));
        $this->assertFalse($policy->delete($user, $order));
    }

    public function test_assigned_executor_can_view_but_cannot_edit_the_parent_order(): void
    {
        $user = $this->user(8, ['view_order', 'update_order']);
        $order = $this->order(42, [8]);
        $policy = new OrderPolicy;

        $this->assertTrue($policy->view($user, $order));
        $this->assertFalse($policy->update($user, $order));
    }

    public function test_creator_and_admin_can_manage_with_the_required_permission(): void
    {
        $order = $this->order(42, [8]);
        $policy = new OrderPolicy;

        $this->assertTrue($policy->update($this->user(42, ['update_order']), $order));
        $this->assertTrue($policy->update($this->user(9, ['update_order'], true), $order));
        $this->assertFalse($policy->update($this->user(9, [], true), $order));
    }

    /** @param array<string> $permissions */
    private function user(int $id, array $permissions, bool $admin = false): User
    {
        $user = new class extends User
        {
            /** @var array<string> */
            public array $allowedPermissions = [];

            public bool $isAdministrator = false;

            public function can($abilities, $arguments = []): bool
            {
                return in_array($abilities, $this->allowedPermissions, true);
            }

            public function hasRole($roles, ?string $guard = null): bool
            {
                return $this->isAdministrator;
            }
        };

        $user->allowedPermissions = $permissions;
        $user->isAdministrator = $admin;
        $user->setAttribute('id', $id);

        return $user;
    }

    /** @param array<int> $executorIds */
    private function order(int $creatorId, array $executorIds): Order
    {
        $order = new Order;
        $order->setAttribute('user_id', $creatorId);
        $order->setRelation('executor', collect($executorIds)->map(function (int $id): Executor {
            $executor = new Executor;
            $executor->setAttribute('user_id', $id);

            return $executor;
        }));

        return $order;
    }
}
