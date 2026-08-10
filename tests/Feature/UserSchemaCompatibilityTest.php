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
            $table->timestamps();
        });
    }

    public function test_user_lookup_matches_the_existing_users_schema_without_deleted_at(): void
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
}
