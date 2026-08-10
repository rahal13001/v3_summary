<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\UserPolicy;
use Tests\TestCase;

class UserDeletionPolicyTest extends TestCase
{
    public function test_user_deletion_uses_soft_delete_and_permanent_delete_remains_disabled(): void
    {
        $actor = new class extends User
        {
            public function can($abilities, $arguments = []): bool
            {
                return true;
            }
        };
        $target = new User;
        $policy = new UserPolicy;

        $this->assertTrue($policy->delete($actor, $target));
        $this->assertTrue($policy->deleteAny($actor));
        $this->assertFalse($policy->forceDelete($actor, $target));
        $this->assertFalse($policy->forceDeleteAny($actor));
        $this->assertTrue($policy->restore($actor, $target));
        $this->assertTrue($policy->restoreAny($actor));
    }
}
