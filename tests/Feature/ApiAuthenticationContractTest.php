<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiAuthenticationContractTest extends TestCase
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
            $table->string('avatar_url')->nullable();
            $table->string('nip')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('status')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_invalid_credentials_preserve_the_existing_error_contract(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.test',
            'password' => 'incorrect-password',
        ])->assertStatus(422)->assertExactJson([
            'success' => false,
            'data' => null,
            'message' => 'The provided credentials are incorrect.',
        ]);
    }

    public function test_login_preserves_the_existing_success_contract_and_user_shape(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('message', 'Login success')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token');

        $this->assertIsString($response->json('data.access_token'));
        $this->assertSame([
            'id',
            'name',
            'email',
            'email_verified_at',
            'avatar_url',
            'nip',
            'jabatan',
            'fcm_token',
            'status',
            'deleted_at',
            'created_at',
            'updated_at',
        ], array_keys($response->json('data.user')));
    }

    public function test_an_existing_sanctum_token_remains_valid_for_the_user_endpoint(): void
    {
        $user = $this->createUser();
        $plainTextToken = 'existing-token-secret';
        $tokenId = DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'existing-consumer-token',
            'token' => hash('sha256', $plainTextToken),
            'abilities' => '["*"]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withToken($tokenId.'|'.$plainTextToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email)
            ->assertJsonMissingPath('password')
            ->assertJsonMissingPath('remember_token');
    }

    public function test_the_user_endpoint_rejects_requests_without_a_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_an_inactive_user_cannot_log_in_and_no_token_is_created(): void
    {
        $user = $this->createUser([
            'email' => 'inactive@example.test',
            'status' => '0',
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertStatus(422)->assertExactJson([
            'success' => false,
            'data' => null,
            'message' => 'The provided credentials are incorrect.',
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_is_rate_limited_after_five_attempts_per_email_and_ip(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'rate-limited@example.test',
                'password' => 'incorrect-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/login', [
            'email' => 'rate-limited@example.test',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_logout_revokes_only_the_current_access_token(): void
    {
        $user = $this->createUser(['email' => 'logout@example.test']);
        $currentToken = $user->createToken('current-consumer-token');
        $otherToken = $user->createToken('other-consumer-token');

        $this->withToken($currentToken->plainTextToken)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => null,
                'message' => 'Logout success',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);

        $this->app->make('auth')->forgetGuards();

        $this->withToken($currentToken->plainTextToken)
            ->getJson('/api/user')
            ->assertUnauthorized();

        $this->withToken($otherToken->plainTextToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    private function createUser(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'SSO Contract User',
            'email' => 'sso@example.test',
            'password' => Hash::make('secret-password'),
            'avatar_url' => '/storage/avatars/sso.jpg',
            'nip' => '1987654321',
            'jabatan' => 'Administrator',
            'fcm_token' => 'contract-fcm-token',
            'status' => 'active',
        ], $overrides));
    }
}
