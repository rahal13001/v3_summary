<?php

namespace Tests\Feature;

use App\Exports\ReportsExport;
use App\Models\Indicator;
use App\Models\Involvement;
use App\Models\Report;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Database\Eloquent\Collection;
use ReflectionClass;
use Tests\TestCase;

class ReportOrganizationOutputsTest extends TestCase
{
    public function test_excel_export_includes_organization_dimensions(): void
    {
        $report = new Report([
            'no_st' => 'ST-001',
            'what' => 'Kegiatan uji',
            'why' => 'Pengujian',
            'when' => '2026-08-11',
            'tanggal_selesai' => '2026-08-12',
            'where' => 'Sorong',
            'who' => 'Pegawai',
            'how' => '<p>Pelaksanaan</p>',
            'penyelenggara' => 'LPRL Sorong',
            'total_peserta' => '10',
            'total_wanita' => '50',
        ]);
        $report->setRelation('user', (new User)->forceFill(['name' => 'Penyusun']));
        $report->setRelation('followers', new Collection([
            (new User)->forceFill(['name' => 'Pengikut']),
        ]));
        $report->setRelation('indicators', new Collection([
            (new Indicator)->forceFill(['nama_iku' => 'IKU 1']),
        ]));
        $report->setRelation('teams', new Collection([
            (new Team)->forceFill(['nama_tim' => 'Tim A']),
        ]));
        $report->setRelation('workUnits', new Collection([
            (new WorkUnit)->forceFill(['name' => 'Wilker Raja Ampat']),
            (new WorkUnit)->forceFill(['name' => 'Gerai Sorong']),
        ]));
        $report->setRelation('involvement', (new Involvement)->forceFill(['name' => 'Penyelenggara']));

        $export = new ReportsExport(new Collection([$report]));
        $row = array_combine($export->headings(), $export->map($report));

        $this->assertSame('Wilker Raja Ampat, Gerai Sorong', $row['Unit Kerja']);
        $this->assertSame('Penyelenggara', $row['Keterlibatan']);
        $this->assertSame('LPRL Sorong', $row['Penyelenggara']);
        $this->assertCount(count($export->headings()), $export->map($report));

        $source = file_get_contents((new ReflectionClass(ReportsExport::class))->getFileName());
        $this->assertStringContainsString("'workUnits'", $source);
        $this->assertStringContainsString("'involvement'", $source);
    }

    public function test_pdf_includes_organization_dimensions_and_eager_loads_them(): void
    {
        $controller = file_get_contents(base_path('app/Http/Controllers/PdfController.php'));
        $view = file_get_contents(resource_path('views/pdf/pdf.blade.php'));

        $this->assertStringContainsString('$report->loadMissing([', $controller);
        $this->assertStringContainsString("'workUnits'", $controller);
        $this->assertStringContainsString("'involvement'", $controller);
        $this->assertStringContainsString('<td>Unit Kerja</td>', $view);
        $this->assertStringContainsString('$report->workUnits', $view);
        $this->assertStringContainsString('<td>Keterlibatan</td>', $view);
        $this->assertStringContainsString('$report->involvement?->name', $view);
        $this->assertStringContainsString('<td>Penyelenggara</td>', $view);
    }

    public function test_excel_export_neutralizes_spreadsheet_formulas(): void
    {
        $report = new Report([
            'what' => '=2+2',
            'total_wanita' => '0',
        ]);
        $report->setRelation('user', null);
        $report->setRelation('followers', new Collection);
        $report->setRelation('indicators', new Collection);
        $report->setRelation('teams', new Collection);
        $report->setRelation('workUnits', new Collection([
            (new WorkUnit)->forceFill(['name' => '@SUM(1,1)']),
        ]));
        $report->setRelation('involvement', (new Involvement)->forceFill(['name' => '+cmd']));

        $export = new ReportsExport(new Collection([$report]));
        $row = array_combine($export->headings(), $export->map($report));

        $this->assertSame("'=2+2", $row['What']);
        $this->assertSame("'@SUM(1,1)", $row['Unit Kerja']);
        $this->assertSame("'+cmd", $row['Keterlibatan']);
    }
}
