<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REVISION_TIMELINE_INDEX = 'report_eval_revisions_evaluation_changed_idx';

    public function up(): void
    {
        if (! Schema::hasTable('report_evaluations')) {
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
        }

        if (! Schema::hasTable('report_evaluation_revisions')) {
            Schema::create('report_evaluation_revisions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('report_evaluation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
                $table->timestamp('changed_at');
                $table->json('changes');

                $table->index(
                    ['report_evaluation_id', 'changed_at'],
                    self::REVISION_TIMELINE_INDEX,
                );
            });

            return;
        }

        if (! Schema::hasIndex('report_evaluation_revisions', self::REVISION_TIMELINE_INDEX)) {
            Schema::table('report_evaluation_revisions', function (Blueprint $table): void {
                $table->index(
                    ['report_evaluation_id', 'changed_at'],
                    self::REVISION_TIMELINE_INDEX,
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_evaluation_revisions');
        Schema::dropIfExists('report_evaluations');
    }
};
