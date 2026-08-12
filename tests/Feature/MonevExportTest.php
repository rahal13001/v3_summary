<?php

namespace Tests\Feature;

use App\Exports\MonevExport;
use App\Filament\Resources\ReportResource;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Models\WorkUnit;
use App\Policies\ReportEvaluationPolicy;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MonevExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_workbook_matches_mapping_merges_print_setup_and_formula_safety(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('coordinator-signatures/signature.png', $this->png());
        OrganizationSetting::query()->create([
            'name' => 'BPS Kota Kupang',
            'short_name' => 'BPS Kupang',
            'monev_enabled' => true,
        ]);
        $unit = WorkUnit::factory()->create(['name' => 'Satuan Pelayanan Kupang']);
        $coordinator = User::factory()->create([
            'name' => 'Koordinator Aman',
            'nip' => '198001012000011001',
            'jabatan' => 'Koordinator Satuan Pelayanan',
            'coordinator_signature_path' => 'coordinator-signatures/signature.png',
        ]);
        $dataset = [
            'INTERNAL' => [[
                'number' => 1,
                'activity_name' => '=SUM(1,1)',
                'implementation_plan' => '+cmd',
                'implementation_realization' => '01-07-2026',
                'activity_result' => '@hasil',
                'obstacles' => '-kendala',
                'recommendations' => 'Saran',
                'follow_up' => 'Tindak lanjut',
                'evidence_links' => ['https://example.test/a', 'https://example.test/b'],
                'notes' => 'Ket',
            ]],
            'EKSTERNAL' => [],
        ];

        $bytes = Excel::raw(new MonevExport(
            $unit,
            Carbon::parse('2026-07-01'),
            'Kupang',
            Carbon::parse('2026-08-01'),
            $coordinator,
            $dataset,
        ), \Maatwebsite\Excel\Excel::XLSX);
        $path = storage_path('framework/testing-monev.xlsx');
        file_put_contents($path, $bytes);
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertSame('EVALUASI RENCANA AKSI / PELAKSANAAN KEGIATAN', $sheet->getCell('A1')->getValue());
        $this->assertSame('BPS KOTA KUPANG', $sheet->getCell('A2')->getValue());
        $this->assertSame('SATUAN PELAYANAN KUPANG', $sheet->getCell('A3')->getValue());
        $this->assertSame('BULAN JULI TAHUN 2026', $sheet->getCell('A4')->getValue());
        $this->assertContains('C6:D6', $sheet->getMergeCells());
        $this->assertContains('A8:J8', $sheet->getMergeCells());
        $this->assertContains('A10:J10', $sheet->getMergeCells());
        $this->assertSame("'=SUM(1,1)", $sheet->getCell('B9')->getValue());
        $this->assertSame("'+cmd", $sheet->getCell('C9')->getValue());
        $this->assertSame("'@hasil", $sheet->getCell('E9')->getValue());
        $this->assertSame("'-kendala", $sheet->getCell('F9')->getValue());
        $this->assertSame("https://example.test/a\nhttps://example.test/b", $sheet->getCell('I9')->getValue());
        $this->assertSame('https://example.test/a', $sheet->getCell('I9')->getHyperlink()->getUrl());
        $this->assertSame(PageSetup::ORIENTATION_LANDSCAPE, $sheet->getPageSetup()->getOrientation());
        $this->assertSame(1, $sheet->getPageSetup()->getFitToWidth());
        $this->assertSame(['6', '7'], $sheet->getPageSetup()->getRowsToRepeatAtTop());
        $this->assertSame('Koordinator Aman', $sheet->getCell('H21')->getValue());
        $this->assertCount(1, $sheet->getDrawingCollection());
        @unlink($path);
    }

    public function test_export_action_is_feature_and_permission_gated_and_scopes_units(): void
    {
        $user = User::factory()->create();
        $mine = WorkUnit::factory()->create();
        WorkUnit::factory()->create();
        Permission::findOrCreate(ReportEvaluationPolicy::EXPORT);
        $user->givePermissionTo(ReportEvaluationPolicy::EXPORT);
        $mine->coordinatorAssignments()->create([
            'user_id' => $user->id,
            'starts_at' => today()->subDay(),
        ]);
        $this->actingAs($user);

        $this->assertTrue(app(ReportEvaluationPolicy::class)->canExportAny($user));
        $this->assertSame([$mine->id => $mine->name], ReportResource::monevWorkUnitOptions());

        $source = file_get_contents(app_path('Filament/Resources/ReportResource.php'));
        $this->assertStringContainsString("Action::make('export_monev')", $source);
        $this->assertStringContainsString('->headerActions([', $source);
        $this->assertStringContainsString('OrganizationContext::class)->monevEnabled()', $source);
    }

    public function test_export_requires_complete_coordinator_profile_and_safe_signature(): void
    {
        Storage::fake('local');
        $coordinator = User::factory()->create([
            'name' => 'Koordinator',
            'nip' => null,
            'jabatan' => null,
            'coordinator_signature_path' => null,
        ]);

        try {
            ReportResource::validateMonevCoordinator($coordinator);
            $this->fail('Validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('coordinator.nip', $exception->errors());
            $this->assertArrayHasKey('coordinator.jabatan', $exception->errors());
            $this->assertArrayHasKey('coordinator.signature', $exception->errors());
        }
    }

    public function test_export_validation_failure_is_reported_as_visible_notification(): void
    {
        Storage::fake('local');

        try {
            ReportResource::ensureMonevCoordinatorIsExportable(null);
            $this->fail('Export should halt when no active coordinator is available.');
        } catch (Halt) {
            // The action remains open so the user can correct the selected work unit.
        }

        Notification::assertNotified(
            Notification::make()
                ->title('Export Monev belum dapat dilakukan')
                ->body('Unit kerja belum memiliki koordinator aktif. Atur koordinator terlebih dahulu sebelum melakukan export.')
                ->danger()
                ->persistent(),
        );
    }

    private function png(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }
}
