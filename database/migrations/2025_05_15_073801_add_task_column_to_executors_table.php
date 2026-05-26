<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('executors', function (Blueprint $table) {
            $table->text('task')->nullable(); // Add the 'task' column with a nullable string
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('executors', function (Blueprint $table) {
            $table->dropColumn('task'); // Remove the 'tasks' column
        });
    }
};
