<?php

namespace App\Policies;

use App\Models\OrganizationSetting;
use App\Models\User;

class OrganizationSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_organization_setting');
    }

    public function view(User $user, OrganizationSetting $setting): bool
    {
        return $user->can('view_organization_setting');
    }

    public function create(User $user): bool
    {
        return $user->can('create_organization_setting');
    }

    public function update(User $user, OrganizationSetting $setting): bool
    {
        return $user->can('update_organization_setting');
    }

    public function delete(User $user, OrganizationSetting $setting): bool
    {
        return false;
    }
}
