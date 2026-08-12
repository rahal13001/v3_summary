<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('coordinator_signature_path')->nullable()->after('jabatan');
        });

        Schema::create('work_unit_coordinators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->timestamps();

            $table->index(['work_unit_id', 'starts_at', 'ends_at']);
            $table->index(['user_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_unit_coordinators');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('coordinator_signature_path');
        });
    }
};
