<?php

namespace App\Policies;

use App\Models\Involvement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvolvementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_involvement');
    }

    public function view(User $user, Involvement $involvement): bool
    {
        return $user->can('view_involvement');
    }

    public function create(User $user): bool
    {
        return $user->can('create_involvement');
    }

    public function update(User $user, Involvement $involvement): bool
    {
        return $user->can('update_involvement');
    }

    public function delete(User $user, Involvement $involvement): bool
    {
        return $user->can('delete_involvement');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_involvement');
    }

    public function forceDelete(User $user, Involvement $involvement): bool
    {
        return $user->can('force_delete_involvement');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_involvement');
    }

    public function restore(User $user, Involvement $involvement): bool
    {
        return $user->can('restore_involvement');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_involvement');
    }

    public function replicate(User $user, Involvement $involvement): bool
    {
        return $user->can('replicate_involvement');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_involvement');
    }
}
