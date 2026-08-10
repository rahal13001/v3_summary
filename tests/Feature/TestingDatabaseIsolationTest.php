<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestingDatabaseIsolationTest extends TestCase
{
    public function test_automated_tests_are_isolated_from_the_mysql_database(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}
