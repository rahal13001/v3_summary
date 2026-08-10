<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use App\Policies\ReportPolicy;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportAuthorizationPolicyTest extends TestCase
{
    public function test_unrelated_user_cannot_modify_a_report_even_with_generic_permissions(): void
    {
        $user = $this->user(7, ['update_report', 'delete_report']);
        $report = $this->report(42);
        $policy = new ReportPolicy;

        $this->assertFalse($policy->update($user, $report));
        $this->assertFalse($policy->delete($user, $report));
    }

    public function test_owner_or_follower_can_update_when_the_permission_is_present(): void
    {
        $owner = $this->user(42, ['update_report']);
        $follower = $this->user(8, ['update_report']);
        $report = $this->report(42, collect([$follower]));
        $policy = new ReportPolicy;

        $this->assertTrue($policy->update($owner, $report));
        $this->assertTrue($policy->update($follower, $report));
    }

    public function test_admin_still_needs_the_resource_permission_to_modify_a_report(): void
    {
        $report = $this->report(42);
        $policy = new ReportPolicy;

        $this->assertTrue($policy->update($this->user(9, ['update_report'], true), $report));
        $this->assertFalse($policy->update($this->user(9, [], true), $report));
    }

    public function test_generic_report_view_permission_still_allows_shared_read_access(): void
    {
        $this->assertTrue(
            (new ReportPolicy)->view($this->user(7, ['view_report']), $this->report(42)),
        );
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

    /** @param Collection<int, User>|null $followers */
    private function report(int $ownerId, ?Collection $followers = null): Report
    {
        $report = new Report;
        $report->setAttribute('user_id', $ownerId);
        $report->setRelation('followers', $followers ?? collect());

        return $report;
    }
}
