<?php

namespace Tests\Feature;

use App\Models\User;
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

    private function createReport(?int $involvementId = null): int
    {
        return DB::table('reports')->insertGetId([
            'user_id' => User::factory()->create()->id,
            'involvement_id' => $involvementId,
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
        ]);
    }
}
