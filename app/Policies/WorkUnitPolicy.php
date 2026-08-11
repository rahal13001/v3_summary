<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkUnitPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_work_unit');
    }

    public function view(User $user, WorkUnit $workUnit): bool
    {
        return $user->can('view_work_unit');
    }

    public function create(User $user): bool
    {
        return $user->can('create_work_unit');
    }

    public function update(User $user, WorkUnit $workUnit): bool
    {
        return $user->can('update_work_unit');
    }

    public function delete(User $user, WorkUnit $workUnit): bool
    {
        return $user->can('delete_work_unit');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_work_unit');
    }

    public function forceDelete(User $user, WorkUnit $workUnit): bool
    {
        return $user->can('force_delete_work_unit');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_work_unit');
    }

    public function restore(User $user, WorkUnit $workUnit): bool
    {
        return $user->can('restore_work_unit');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_work_unit');
    }

    public function replicate(User $user, WorkUnit $workUnit): bool
    {
        return $user->can('replicate_work_unit');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_work_unit');
    }
}
