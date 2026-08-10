<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\UserPolicy;
use Tests\TestCase;

class UserDeletionPolicyTest extends TestCase
{
    public function test_user_deletion_lifecycle_is_disabled_until_data_preservation_is_defined(): void
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

        $this->assertFalse($policy->delete($actor, $target));
        $this->assertFalse($policy->deleteAny($actor));
        $this->assertFalse($policy->forceDelete($actor, $target));
        $this->assertFalse($policy->forceDeleteAny($actor));
        $this->assertFalse($policy->restore($actor, $target));
        $this->assertFalse($policy->restoreAny($actor));
    }
}
