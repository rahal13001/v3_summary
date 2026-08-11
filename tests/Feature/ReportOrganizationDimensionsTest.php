<?php

namespace Tests\Feature;

use App\Models\Involvement;
use App\Models\Report;
use App\Models\User;
use App\Models\WorkUnit;
use Database\Seeders\InvolvementSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportOrganizationDimensionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_dimension_schema_is_available(): void
    {
        $this->assertTrue(Schema::hasColumns('work_units', [
            'id',
            'name',
            'status',
            'unit',
            'created_at',
            'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('involvements', [
            'id',
            'name',
            'status',
            'is_lprl_organizer',
            'created_at',
            'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('report_work_unit', [
            'report_id',
            'work_unit_id',
        ]));
        $this->assertTrue(Schema::hasColumn('reports', 'involvement_id'));
    }

    public function test_report_work_unit_pairs_are_unique(): void
    {
        $workUnitId = DB::table('work_units')->insertGetId([
            'name' => 'Kantor Sorong',
            'status' => 'active',
            'unit' => 'satuan_pelayanan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $reportId = $this->createReport();

        DB::table('report_work_unit')->insert([
            'report_id' => $reportId,
            'work_unit_id' => $workUnitId,
        ]);

        $this->expectException(QueryException::class);

        DB::table('report_work_unit')->insert([
            'report_id' => $reportId,
            'work_unit_id' => $workUnitId,
        ]);
    }

    public function test_referenced_work_units_cannot_be_deleted(): void
    {
        $workUnitId = DB::table('work_units')->insertGetId([
            'name' => 'Kantor Sorong',
            'status' => 'active',
            'unit' => 'satuan_pelayanan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('report_work_unit')->insert([
            'report_id' => $this->createReport(),
            'work_unit_id' => $workUnitId,
        ]);

        $this->expectException(QueryException::class);

        DB::table('work_units')->where('id', $workUnitId)->delete();
    }

    public function test_referenced_involvements_cannot_be_deleted(): void
    {
        $involvementId = DB::table('involvements')->insertGetId([
            'name' => 'Peserta',
            'status' => 'active',
            'is_lprl_organizer' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->createReport($involvementId);

        $this->expectException(QueryException::class);

        DB::table('involvements')->where('id', $involvementId)->delete();
    }

    public function test_report_and_work_unit_have_a_many_to_many_relationship(): void
    {
        $report = Report::query()->findOrFail($this->createReport());
        $workUnit = WorkUnit::query()->create([
            'name' => 'Wilker Raja Ampat',
            'status' => WorkUnit::STATUS_ACTIVE,
            'unit' => WorkUnit::UNIT_WORK_AREA,
        ]);

        $report->workUnits()->attach($workUnit);

        $this->assertTrue($report->fresh()->workUnits->contains($workUnit));
        $this->assertTrue($workUnit->fresh()->reports->contains($report));
    }

    public function test_report_belongs_to_one_involvement(): void
    {
        $involvement = Involvement::query()->create([
            'name' => 'Sponsor',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => false,
        ]);
        $report = Report::query()->findOrFail($this->createReport($involvement->id));

        $this->assertTrue($report->involvement->is($involvement));
        $this->assertTrue($involvement->reports->contains($report));
    }

    public function test_master_options_use_stable_codes_and_indonesian_labels(): void
    {
        $this->assertSame([
            WorkUnit::UNIT_SERVICE_UNIT => 'Satuan Pelayanan',
            WorkUnit::UNIT_WORK_AREA => 'Wilayah Kerja',
            WorkUnit::UNIT_SERVICE_OUTLET => 'Gerai Pelayanan',
        ], WorkUnit::unitOptions());
        $this->assertSame([
            WorkUnit::STATUS_ACTIVE => 'Aktif',
            WorkUnit::STATUS_INACTIVE => 'Tidak Aktif',
        ], WorkUnit::statusOptions());
        $this->assertSame(WorkUnit::statusOptions(), Involvement::statusOptions());
    }

    public function test_involvement_seeder_is_idempotent(): void
    {
        $this->seed(InvolvementSeeder::class);
        $this->seed(InvolvementSeeder::class);

        $this->assertDatabaseCount('involvements', 2);
        $this->assertDatabaseHas('involvements', [
            'name' => 'Penyelenggara',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);
        $this->assertDatabaseHas('involvements', [
            'name' => 'Peserta',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => false,
        ]);
    }

    public function test_lprl_organizer_involvement_forces_the_official_organizer_name(): void
    {
        $involvement = Involvement::query()->create([
            'name' => 'Penyelenggara',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);

        $report = Report::query()->create($this->reportAttributes([
            'involvement_id' => $involvement->id,
            'penyelenggara' => 'Nilai yang dimanipulasi',
        ]));

        $this->assertSame('LPRL Sorong', $report->penyelenggara);
        $this->assertSame('LPRL Sorong', $involvement->organizerName());
    }

    public function test_external_involvement_has_no_automatic_organizer_name(): void
    {
        $involvement = Involvement::query()->create([
            'name' => 'Peserta',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => false,
        ]);

        $this->assertNull($involvement->organizerName());
    }

    private function createReport(?int $involvementId = null): int
    {
        return DB::table('reports')->insertGetId($this->reportAttributes([
            'involvement_id' => $involvementId,
        ]));
    }

    private function reportAttributes(array $overrides = []): array
    {
        return array_merge([
            'user_id' => User::factory()->create()->id,
            'slug' => fake()->unique()->slug(),
            'no_st' => 'ST-001',
            'what' => 'Kegiatan uji',
            'why' => 'Pengujian',
            'when' => '2026-08-11',
            'tanggal_selesai' => '2026-08-11',
            'where' => 'Sorong',
            'who' => 'Pegawai',
            'how' => 'Pelaksanaan',
            'penyelenggara' => 'LPRL Sorong',
            'total_peserta' => '10',
            'total_wanita' => '50',
            'kode' => 'signature',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);
    }
}
