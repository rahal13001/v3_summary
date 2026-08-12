<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_evaluations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_unit_id')->constrained()->restrictOnDelete();
            $table->date('period');
            $table->text('rencana_pelaksanaan')->nullable();
            $table->text('kendala')->nullable();
            $table->text('saran_rekomendasi')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->json('evidence_links')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['report_id', 'work_unit_id', 'period'], 'report_evaluation_identity_unique');
            $table->index(['work_unit_id', 'period']);
        });

        Schema::create('report_evaluation_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
            $table->json('changes');

            $table->index(['report_evaluation_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_evaluation_revisions');
        Schema::dropIfExists('report_evaluations');
    }
};
