<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebTokenTest extends TestCase
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
            $table->string('password');
            $table->string('fcm_token')->nullable();
            $table->string('status')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_guest_cannot_access_the_web_token_endpoint(): void
    {
        $this->postJson(route('webtoken'), [
            'fcm_token' => 'guest-token',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_update_only_their_web_token(): void
    {
        $user = $this->createUser('user@example.test', 'old-user-token');
        $otherUser = $this->createUser('other@example.test', 'other-user-token');

        $this->actingAs($user)
            ->postJson(route('webtoken'), [
                'fcm_token' => 'new-user-token',
            ])
            ->assertCreated()
            ->assertExactJson([
                'message' => 'Token berhasil disimpan',
            ]);

        $this->assertSame('new-user-token', $user->refresh()->fcm_token);
        $this->assertSame('other-user-token', $otherUser->refresh()->fcm_token);
    }

    private function createUser(string $email, string $fcmToken): User
    {
        return User::query()->create([
            'name' => 'Web Token User',
            'email' => $email,
            'password' => 'secret-password',
            'fcm_token' => $fcmToken,
            'status' => 'active',
        ]);
    }
}
