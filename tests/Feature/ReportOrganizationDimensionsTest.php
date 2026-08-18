<?php

namespace Tests\Feature;

use App\Models\Involvement;
use App\Models\OrganizationSetting;
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

    public function test_selectable_involvements_include_active_and_current_inactive_records(): void
    {
        $active = Involvement::query()->create([
            'name' => 'Internal',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);
        $currentInactive = Involvement::query()->create([
            'name' => 'Keterlibatan Lama',
            'status' => Involvement::STATUS_INACTIVE,
            'is_lprl_organizer' => false,
        ]);
        $otherInactive = Involvement::query()->create([
            'name' => 'Tidak Tersedia',
            'status' => Involvement::STATUS_INACTIVE,
            'is_lprl_organizer' => false,
        ]);

        $selectableIds = Involvement::query()
            ->selectableForReport($currentInactive->id)
            ->pluck('id')
            ->all();

        $this->assertEqualsCanonicalizing([
            $active->id,
            $currentInactive->id,
        ], $selectableIds);
        $this->assertNotContains($otherInactive->id, $selectableIds);
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

    public function test_editable_internal_organizer_preserves_custom_value_and_defaults_blank_value(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'Balai Pengelolaan Kelautan Kupang',
            'short_name' => 'BPK Kupang',
            'organizer_name' => 'Balai PK Kupang',
            'organizer_input_mode' => OrganizationSetting::ORGANIZER_MODE_EDITABLE,
        ]);
        $internal = Involvement::query()->create([
            'name' => 'Internal',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);

        $custom = Report::query()->findOrFail($this->createReport($internal->id, 'Mitra Kegiatan'));
        $defaulted = Report::query()->findOrFail($this->createReport($internal->id, ''));

        $this->assertSame('Mitra Kegiatan', $custom->penyelenggara);
        $this->assertSame('Balai PK Kupang', $defaulted->penyelenggara);
    }

    public function test_manual_internal_organizer_never_overwrites_user_input(): void
    {
        OrganizationSetting::query()->create([
            'name' => 'Balai Pengelolaan Kelautan Kupang',
            'short_name' => 'BPK Kupang',
            'organizer_name' => 'Balai PK Kupang',
            'organizer_input_mode' => OrganizationSetting::ORGANIZER_MODE_MANUAL,
        ]);
        $internal = Involvement::query()->create([
            'name' => 'Internal',
            'status' => Involvement::STATUS_ACTIVE,
            'is_lprl_organizer' => true,
        ]);

        $report = Report::query()->findOrFail($this->createReport($internal->id, 'Penyelenggara Manual'));

        $this->assertSame('Penyelenggara Manual', $report->penyelenggara);
    }

    private function createReport(?int $involvementId = null, string $organizer = 'LPRL Sorong'): int
    {
        return Report::query()->create($this->reportAttributes([
            'involvement_id' => $involvementId,
            'penyelenggara' => $organizer,
        ]))->id;
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
