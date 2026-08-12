<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\ReportEvaluationRevision;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\ReportEvaluationService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class ReportEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluation_and_append_only_revision_schema_are_available(): void
    {
        $this->assertTrue(Schema::hasColumns('report_evaluations', [
            'id', 'report_id', 'work_unit_id', 'period',
            'rencana_pelaksanaan', 'kendala', 'saran_rekomendasi',
            'tindak_lanjut', 'evidence_links', 'keterangan',
            'created_by', 'updated_by', 'created_at', 'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('report_evaluation_revisions', [
            'id', 'report_evaluation_id', 'changed_by', 'changed_at', 'changes',
        ]));
    }

    public function test_evaluation_is_unique_per_report_unit_and_normalized_month(): void
    {
        [$report, $unit, $user] = $this->context();
        $service = app(ReportEvaluationService::class);

        $evaluation = $service->create($report, $unit, '2026-08-19', [], $user);

        $this->assertSame('2026-08-01', $evaluation->period->toDateString());

        $this->expectException(QueryException::class);

        ReportEvaluation::query()->create([
            'report_id' => $report->id,
            'work_unit_id' => $unit->id,
            'period' => '2026-08-01',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function test_evaluation_rejects_a_unit_not_attached_to_the_report(): void
    {
        [$report, , $user] = $this->context();

        $this->expectException(ValidationException::class);

        app(ReportEvaluationService::class)->create(
            $report,
            $this->workUnit('Unit lain'),
            '2026-08-01',
            [],
            $user,
        );
    }

    public function test_evidence_links_only_accept_structured_http_and_https_urls(): void
    {
        [$report, $unit, $user] = $this->context();

        $evaluation = app(ReportEvaluationService::class)->create($report, $unit, '2026-08-01', [
            'evidence_links' => [' https://example.test/a ', 'http://example.test/b'],
        ], $user);

        $this->assertSame([
            'https://example.test/a',
            'http://example.test/b',
        ], $evaluation->evidence_links);

        $this->expectException(ValidationException::class);

        app(ReportEvaluationService::class)->update($evaluation, [
            'evidence_links' => ['javascript:alert(1)'],
        ], $user);
    }

    public function test_create_update_and_no_op_write_exact_append_only_diffs(): void
    {
        [$report, $unit, $user] = $this->context();
        $service = app(ReportEvaluationService::class);

        $evaluation = $service->create($report, $unit, '2026-08-01', [
            'rencana_pelaksanaan' => 'Rencana awal',
            'kendala' => null,
        ], $user);

        $created = $evaluation->revisions()->firstOrFail();
        $this->assertSame([
            'report_id' => ['old' => null, 'new' => $report->id],
            'work_unit_id' => ['old' => null, 'new' => $unit->id],
            'period' => ['old' => null, 'new' => '2026-08-01'],
            'rencana_pelaksanaan' => ['old' => null, 'new' => 'Rencana awal'],
        ], $created->changes);

        $service->update($evaluation, [
            'rencana_pelaksanaan' => 'Rencana berubah',
            'kendala' => 'Cuaca',
        ], $user);

        $updated = $evaluation->revisions()->latest('id')->firstOrFail();
        $this->assertSame([
            'rencana_pelaksanaan' => ['old' => 'Rencana awal', 'new' => 'Rencana berubah'],
            'kendala' => ['old' => null, 'new' => 'Cuaca'],
        ], $updated->changes);

        $service->update($evaluation, [
            'rencana_pelaksanaan' => 'Rencana berubah',
            'kendala' => 'Cuaca',
        ], $user);

        $this->assertCount(2, $evaluation->revisions()->get());
    }

    public function test_revision_failure_rolls_back_the_evaluation_create(): void
    {
        [$report, $unit, $user] = $this->context();

        ReportEvaluationRevision::creating(function (): void {
            throw new RuntimeException('revision failed');
        });

        try {
            app(ReportEvaluationService::class)->create($report, $unit, '2026-08-01', [], $user);
            $this->fail('The revision failure should bubble out.');
        } catch (RuntimeException $exception) {
            $this->assertSame('revision failed', $exception->getMessage());
        }

        $this->assertDatabaseCount('report_evaluations', 0);
        $this->assertDatabaseCount('report_evaluation_revisions', 0);
    }

    public function test_revisions_cannot_be_updated_or_deleted(): void
    {
        [$report, $unit, $user] = $this->context();
        $revision = app(ReportEvaluationService::class)
            ->create($report, $unit, '2026-08-01', [], $user)
            ->revisions()
            ->firstOrFail();

        foreach (['update', 'delete'] as $operation) {
            try {
                $operation === 'update'
                    ? $revision->update(['changes' => []])
                    : $revision->delete();

                $this->fail("Revision {$operation} should be rejected.");
            } catch (RuntimeException) {
                $this->assertDatabaseCount('report_evaluation_revisions', 1);
            }
        }
    }

    /** @return array{Report, WorkUnit, User} */
    private function context(): array
    {
        $user = User::factory()->create();
        $report = Report::query()->create($this->reportAttributes($user));
        $unit = $this->workUnit('Unit A');
        $report->workUnits()->attach($unit);

        return [$report, $unit, $user];
    }

    private function workUnit(string $name): WorkUnit
    {
        return WorkUnit::query()->create([
            'name' => $name,
            'status' => WorkUnit::STATUS_ACTIVE,
            'unit' => WorkUnit::UNIT_SERVICE_UNIT,
        ]);
    }

    private function reportAttributes(User $user): array
    {
        return [
            'user_id' => $user->id,
            'slug' => fake()->unique()->slug(),
            'no_st' => 'ST-001',
            'what' => 'Kegiatan uji',
            'why' => 'Pengujian',
            'when' => '2026-08-01',
            'tanggal_selesai' => '2026-08-02',
            'where' => 'Kupang',
            'who' => 'Pegawai',
            'how' => 'Pelaksanaan',
            'penyelenggara' => 'Organisasi',
            'total_peserta' => '10',
            'total_wanita' => '50',
            'kode' => 'signature',
        ];
    }
}
