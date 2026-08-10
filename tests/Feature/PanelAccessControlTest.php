<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Panel;
use Tests\TestCase;

class PanelAccessControlTest extends TestCase
{
    public function test_inactive_users_cannot_access_the_filament_panel_even_with_an_allowed_role(): void
    {
        $user = new class extends User
        {
            public function hasRole($roles, ?string $guard = null): bool
            {
                return true;
            }
        };

        $user->status = 0;

        $this->assertFalse($user->canAccessPanel(Panel::make()));
    }

    public function test_soft_deleted_users_cannot_access_the_filament_panel_even_with_an_allowed_role(): void
    {
        $user = new class extends User
        {
            public function hasRole($roles, ?string $guard = null): bool
            {
                return true;
            }
        };

        $user->status = 1;
        $user->deleted_at = now();

        $this->assertFalse($user->canAccessPanel(Panel::make()));
    }

    public function test_active_users_with_an_allowed_role_can_access_the_filament_panel(): void
    {
        $user = new class extends User
        {
            public function hasRole($roles, ?string $guard = null): bool
            {
                return true;
            }
        };

        $user->status = 1;

        $this->assertTrue($user->canAccessPanel(Panel::make()));
    }
}
