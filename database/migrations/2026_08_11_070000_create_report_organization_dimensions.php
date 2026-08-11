<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status');
            $table->string('unit');
            $table->timestamps();

            $table->unique(['name', 'unit']);
        });

        Schema::create('involvements', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status');
            $table->boolean('is_lprl_organizer')->default(false);
            $table->timestamps();
        });

        Schema::create('report_work_unit', function (Blueprint $table) {
            $table->foreignId('report_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('work_unit_id')
                ->constrained()
                ->restrictOnDelete();

            $table->unique(['report_id', 'work_unit_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('involvement_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('involvement_id');
        });

        Schema::dropIfExists('report_work_unit');
        Schema::dropIfExists('involvements');
        Schema::dropIfExists('work_units');
    }
};
