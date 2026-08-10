<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Intentionally empty. The table is owned by the earlier
        // 2025_06_02_154206 migration. Keeping this migration as a no-op lets
        // existing deployments record it without changing the SSO token table.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally empty. Rolling this duplicate migration back must not
        // drop the table created by the canonical migration.
    }
};
