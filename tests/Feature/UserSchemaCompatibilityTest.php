<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserSchemaCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            // Hosting uses a legacy VARCHAR column; SoftDeletes works with
            // the timestamp string without requiring a production type change.
            $table->string('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_user_lookup_uses_the_hosting_soft_delete_column(): void
    {
        User::query()->create([
            'name' => 'Login User',
            'email' => 'login@example.test',
            'password' => 'hashed-password',
        ]);

        $user = User::query()->where('email', 'login@example.test')->first();

        $this->assertNotNull($user);
        $this->assertSame('Login User', $user->name);
    }

    public function test_soft_deleted_users_are_hidden_and_can_be_restored(): void
    {
        $user = User::query()->create([
            'name' => 'Deleted User',
            'email' => 'deleted@example.test',
            'password' => 'hashed-password',
        ]);

        $user->delete();

        $this->assertNull(User::query()->find($user->id));
        $this->assertTrue(User::withTrashed()->findOrFail($user->id)->trashed());

        $user->restore();

        $this->assertNotNull(User::query()->find($user->id));
        $this->assertNull(User::findOrFail($user->id)->deleted_at);
    }
}
