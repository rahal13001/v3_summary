<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_order');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->can('view_order') && ($this->canManage($user, $order) || $this->isExecutor($user, $order));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_order');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('update_order') && $this->canManage($user, $order);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('delete_order') && $this->canManage($user, $order);
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->can('force_delete_order') && $this->canManage($user, $order);
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->can('restore_order') && $this->canManage($user, $order);
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Order $order): bool
    {
        return $user->can('replicate_order') && $this->canManage($user, $order);
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_order');
    }

    private function canManage(User $user, Order $order): bool
    {
        return $user->hasRole(['admin', 'super_admin'])
            || ($order->user_id !== null && (string) $order->user_id === (string) $user->getKey());
    }

    private function isExecutor(User $user, Order $order): bool
    {
        return $order->executor->contains(
            fn ($executor): bool => (string) $executor->user_id === (string) $user->getKey(),
        );
    }
}
