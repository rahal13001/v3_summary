<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonalAccessTokenMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    public function test_duplicate_sanctum_migrations_are_safe_on_a_fresh_database(): void
    {
        $first = require database_path('migrations/2025_06_02_154206_create_personal_access_tokens_table.php');
        $second = require database_path('migrations/2025_06_02_154547_create_personal_access_tokens_table.php');

        $first->up();
        $second->up();

        $this->assertTrue(Schema::hasTable('personal_access_tokens'));
        $this->assertSame(
            ['id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at', 'created_at', 'updated_at'],
            Schema::getColumnListing('personal_access_tokens'),
        );

        $second->down();
        $this->assertTrue(Schema::hasTable('personal_access_tokens'));

        $first->down();
        $this->assertFalse(Schema::hasTable('personal_access_tokens'));
    }
}
