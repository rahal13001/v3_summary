<?php

namespace Tests\Feature;

use App\Models\Involvement;
use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\MonevDatasetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MonevDatasetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dataset_requires_unit_and_includes_inclusive_overlap_or_period_evaluation(): void
    {
        $unit = WorkUnit::factory()->create();
        $otherUnit = WorkUnit::factory()->create();
        $internal = Involvement::query()->create([
            'name' => 'Penyelenggara',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);

        $startsOnEnd = $this->report($internal, 'Mulai akhir bulan', '2026-07-31', '2026-08-02');
        $endsOnStart = $this->report($internal, 'Selesai awal bulan', '2026-06-20', '2026-07-01');
        $oldWithEvaluation = $this->report($internal, 'Lama tetapi dievaluasi', '2025-01-01', '2025-01-02');
        $oldWithoutEvaluation = $this->report($internal, 'Lama tanpa evaluasi', '2025-02-01', '2025-02-02');
        $otherUnitReport = $this->report($internal, 'Unit lain', '2026-07-10', '2026-07-11');

        foreach ([$startsOnEnd, $endsOnStart, $oldWithEvaluation, $oldWithoutEvaluation] as $report) {
            $report->workUnits()->attach($unit);
        }
        $otherUnitReport->workUnits()->attach($otherUnit);
        $this->evaluation($oldWithEvaluation, $unit, '2026-07-01');

        $dataset = app(MonevDatasetService::class)->build($unit, '2026-07-01');
        $names = collect($dataset['INTERNAL'])->pluck('activity_name')->all();

        $this->assertSame([
            'Lama tetapi dievaluasi',
            'Selesai awal bulan',
            'Mulai akhir bulan',
        ], $names);
        $this->assertNotContains('Lama tanpa evaluasi', $names);
        $this->assertNotContains('Unit lain', $names);
    }

    public function test_dataset_groups_by_organizer_flag_orders_rows_and_resets_numbers(): void
    {
        $unit = WorkUnit::factory()->create();
        $internal = Involvement::query()->create([
            'name' => 'Nama apa pun',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);
        $external = Involvement::query()->create([
            'name' => 'BPS Kupang sekalipun',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => false,
        ]);
        $laterAlphabetically = $this->report($internal, 'Zulu', '2026-07-02', '2026-07-02');
        $earlierAlphabetically = $this->report($internal, 'Alpha', '2026-07-02', '2026-07-03');
        $externalReport = $this->report($external, 'Eksternal', '2026-07-01', '2026-07-01');
        $missingInvolvement = $this->report(null, 'Tanpa master', '2026-07-03', '2026-07-03');

        foreach ([$laterAlphabetically, $earlierAlphabetically, $externalReport, $missingInvolvement] as $report) {
            $report->workUnits()->attach($unit);
        }

        $dataset = app(MonevDatasetService::class)->build($unit, '2026-07-15');

        $this->assertSame(['Alpha', 'Zulu'], collect($dataset['INTERNAL'])->pluck('activity_name')->all());
        $this->assertSame([1, 2], collect($dataset['INTERNAL'])->pluck('number')->all());
        $this->assertSame(['Eksternal', 'Tanpa master'], collect($dataset['EKSTERNAL'])->pluck('activity_name')->all());
        $this->assertSame([1, 2], collect($dataset['EKSTERNAL'])->pluck('number')->all());
    }

    public function test_dataset_maps_evaluation_and_plain_text_with_empty_defaults(): void
    {
        $unit = WorkUnit::factory()->create();
        $report = $this->report(null, 'Kegiatan', '2026-07-05', '2026-07-06', '<p>Hasil <strong>aman</strong>&amp; selesai</p>');
        $withoutEvaluation = $this->report(null, 'Kosong', '2026-07-07', '2026-07-07');
        $report->workUnits()->attach($unit);
        $withoutEvaluation->workUnits()->attach($unit);
        $this->evaluation($report, $unit, '2026-07-01', [
            'rencana_pelaksanaan' => 'Rencana',
            'kendala' => 'Kendala',
            'saran_rekomendasi' => 'Saran',
            'tindak_lanjut' => 'Tindak lanjut',
            'evidence_links' => ['https://example.test/a', 'https://example.test/b'],
            'keterangan' => 'Ket',
        ]);

        $dataset = app(MonevDatasetService::class)->build($unit, '2026-07-01');
        $row = $dataset['EKSTERNAL'][0];

        $this->assertSame([
            'number' => 1,
            'activity_name' => 'Kegiatan',
            'implementation_plan' => 'Rencana',
            'implementation_realization' => '05-07-2026 s.d. 06-07-2026',
            'activity_result' => 'Hasil aman& selesai',
            'obstacles' => 'Kendala',
            'recommendations' => 'Saran',
            'follow_up' => 'Tindak lanjut',
            'evidence_links' => ['https://example.test/a', 'https://example.test/b'],
            'notes' => 'Ket',
        ], $row);
        $this->assertSame('', $dataset['EKSTERNAL'][1]['implementation_plan']);
        $this->assertSame([], $dataset['EKSTERNAL'][1]['evidence_links']);
    }

    public function test_dataset_excludes_soft_deleted_reports_and_has_bounded_queries(): void
    {
        $unit = WorkUnit::factory()->create();

        foreach (range(1, 5) as $index) {
            $report = $this->report(null, "Kegiatan {$index}", "2026-07-0{$index}", "2026-07-0{$index}");
            $report->workUnits()->attach($unit);
        }
        $deleted = $this->report(null, 'Dihapus', '2026-07-10', '2026-07-10');
        $deleted->workUnits()->attach($unit);
        $deleted->delete();

        DB::flushQueryLog();
        DB::enableQueryLog();
        $dataset = app(MonevDatasetService::class)->build($unit, '2026-07-01');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount(5, $dataset['EKSTERNAL']);
        $this->assertLessThanOrEqual(3, $queryCount);
    }

    private function report(
        ?Involvement $involvement,
        string $what,
        string $start,
        string $end,
        string $how = 'Hasil',
    ): Report {
        return Report::factory()->for(User::factory())->create([
            'involvement_id' => $involvement?->id,
            'what' => $what,
            'when' => $start,
            'tanggal_selesai' => $end,
            'how' => $how,
        ]);
    }

    private function evaluation(Report $report, WorkUnit $unit, string $period, array $attributes = []): ReportEvaluation
    {
        $editor = User::factory()->create();

        return ReportEvaluation::factory()->create([
            'report_id' => $report->id,
            'work_unit_id' => $unit->id,
            'period' => $period,
            'created_by' => $editor->id,
            'updated_by' => $editor->id,
            ...$attributes,
        ]);
    }
}
