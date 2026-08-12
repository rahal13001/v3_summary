<?php

namespace App\Exports;

use App\Models\User;
use App\Models\WorkUnit;
use App\Services\MonevDatasetService;
use App\Services\OrganizationContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonevExport implements FromArray, WithEvents
{
    private array $dataset;

    /** @var array<int, array<int, string|int|null>> */
    private array $rows = [];

    /** @var array<int, array<int, string>> */
    private array $linksByRow = [];

    /** @var array<int, int> */
    private array $groupRows = [];

    private int $lastDataRow = 7;

    public function __construct(
        private WorkUnit $workUnit,
        private Carbon $period,
        private string $signingLocation,
        private Carbon $signingDate,
        private User $coordinator,
        ?array $dataset = null,
    ) {
        $this->period = $this->period->copy()->startOfMonth();
        $this->dataset = $dataset ?? app(MonevDatasetService::class)->build($workUnit, $this->period);
        $this->buildRows();
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn (AfterSheet $event) => $this->format($event->sheet->getDelegate()),
        ];
    }

    private function buildRows(): void
    {
        $organization = app(OrganizationContext::class);
        $month = mb_strtoupper($this->period->locale('id')->translatedFormat('F'));

        $this->rows = [
            [$this->safeText('EVALUASI RENCANA AKSI / PELAKSANAAN KEGIATAN')],
            [$this->safeText(mb_strtoupper($organization->name()))],
            [$this->safeText(mb_strtoupper($this->workUnit->name))],
            [$this->safeText("BULAN {$month} TAHUN {$this->period->year}")],
            [''],
            ['No', 'Nama Kegiatan', 'Progres Bulanan Kegiatan', null, 'Hasil Kegiatan', 'Kendala/ Permasalahan Pelaksanaan Kegiatan', 'Saran/ Rekomendasi', 'Tindak Lanjut', 'Link Bukti Tindaklanjut', 'Ket.'],
            [null, null, 'Rencana Pelaksanaan', 'Realisasi Pelaksanaan'],
        ];

        foreach (['INTERNAL', 'EKSTERNAL'] as $group) {
            $this->groupRows[] = count($this->rows) + 1;
            $this->rows[] = [$group];

            foreach ($this->dataset[$group] ?? [] as $item) {
                $rowNumber = count($this->rows) + 1;
                $links = $this->safeLinks((array) ($item['evidence_links'] ?? []));
                $this->linksByRow[$rowNumber] = $links;
                $this->rows[] = [
                    (int) $item['number'],
                    $this->safeText($item['activity_name'] ?? ''),
                    $this->safeText($item['implementation_plan'] ?? ''),
                    $this->safeText($item['implementation_realization'] ?? ''),
                    $this->safeText($item['activity_result'] ?? ''),
                    $this->safeText($item['obstacles'] ?? ''),
                    $this->safeText($item['recommendations'] ?? ''),
                    $this->safeText($item['follow_up'] ?? ''),
                    implode("\n", $links),
                    $this->safeText($item['notes'] ?? ''),
                ];
            }
        }

        $this->lastDataRow = count($this->rows);
    }

    private function format(Worksheet $sheet): void
    {
        $signatureTop = $this->lastDataRow + 3;
        $nameRow = $signatureTop + 8;
        $nipRow = $nameRow + 1;
        $lastRow = $nipRow;

        $sheet->setTitle($this->period->locale('id')->translatedFormat('F Y'));
        $sheet->setShowGridlines(false);

        foreach (range(1, 4) as $row) {
            $sheet->mergeCells("A{$row}:J{$row}");
        }
        foreach (['A6:A7', 'B6:B7', 'C6:D6', 'E6:E7', 'F6:F7', 'G6:G7', 'H6:H7', 'I6:I7', 'J6:J7'] as $range) {
            $sheet->mergeCells($range);
        }
        foreach ($this->groupRows as $row) {
            $sheet->mergeCells("A{$row}:J{$row}");
        }

        $sheet->getStyle("A1:J{$lastRow}")->getFont()->setName('Cambria')->setSize(9);
        $sheet->getStyle('A1:J4')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A1:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $header = $sheet->getStyle('A6:J7');
        $header->getFont()->setBold(true);
        $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D8D8D8');
        $header->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        if ($this->lastDataRow >= 8) {
            $data = $sheet->getStyle("A8:J{$this->lastDataRow}");
            $data->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $data->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A8:A{$this->lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C8:D{$this->lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $header->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach ($this->groupRows as $row) {
            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E6E6');
            $sheet->getRowDimension($row)->setRowHeight(14.25);
        }

        $widths = [5, 17, 19.18, 19, 85.27, 19, 16.91, 15.45, 13.27, 8.82];
        foreach (range('A', 'J') as $index => $column) {
            $sheet->getColumnDimension($column)->setAutoSize(false)->setWidth($widths[$index]);
        }
        $sheet->getRowDimension(7)->setRowHeight(65.5);

        foreach (range(8, $this->lastDataRow) as $row) {
            if (in_array($row, $this->groupRows, true)) {
                continue;
            }

            $maxLength = max(array_map(
                fn (string $column): int => mb_strlen((string) $sheet->getCell("{$column}{$row}")->getValue()),
                range('B', 'J'),
            ));
            $sheet->getRowDimension($row)->setRowHeight(min(240, max(30, 18 + (int) ceil($maxLength / 80) * 12)));
        }

        foreach ($this->linksByRow as $row => $links) {
            $cell = $sheet->getCell("I{$row}");
            $cell->setValueExplicit(implode("\n", $links), DataType::TYPE_STRING);

            if ($links !== []) {
                $cell->getHyperlink()->setUrl($links[0])->setTooltip(implode("\n", $links));
                $sheet->getStyle("I{$row}")->getFont()->getColor()->setRGB('0563C1');
                $sheet->getStyle("I{$row}")->getFont()->setUnderline(true);
            }
        }

        $signatureRange = "H{$signatureTop}:I{$signatureTop}";
        $sheet->mergeCells($signatureRange);
        $sheet->setCellValue("H{$signatureTop}", $this->safeText(
            trim($this->signingLocation).', '.$this->signingDate->locale('id')->translatedFormat('j F Y'),
        ));
        $sheet->mergeCells('H'.($signatureTop + 1).':I'.($signatureTop + 1));
        $sheet->setCellValue('H'.($signatureTop + 1), $this->safeText($this->coordinator->jabatan));
        $sheet->mergeCells("H{$nameRow}:I{$nameRow}");
        $sheet->setCellValue("H{$nameRow}", $this->safeText($this->coordinator->name));
        $sheet->mergeCells("H{$nipRow}:I{$nipRow}");
        $sheet->setCellValue("H{$nipRow}", $this->safeText('NIP '.$this->coordinator->nip));
        $sheet->getStyle("H{$signatureTop}:I{$nipRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->addSignature($sheet, $signatureTop + 2);

        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd(6, 7)
            ->setPrintArea("A1:J{$lastRow}");
        $sheet->getPageMargins()->setLeft(0.25)->setRight(0.25)->setTop(0.75)->setBottom(0.75);
        $sheet->freezePane('A8');
    }

    private function addSignature(Worksheet $sheet, int $row): void
    {
        $path = trim((string) $this->coordinator->coordinator_signature_path);

        if ($path === '' || ! Storage::disk('local')->exists($path)) {
            return;
        }

        $drawing = new Drawing;
        $drawing->setName('Tanda tangan koordinator')
            ->setPath(Storage::disk('local')->path($path))
            ->setCoordinates("H{$row}")
            ->setHeight(72)
            ->setOffsetX(35)
            ->setWorksheet($sheet);
    }

    private function safeText(mixed $value): string
    {
        $text = (string) $value;

        return preg_match('/^[\p{Z}\s]*[=+\-@]/u', $text) === 1 ? "'{$text}" : $text;
    }

    /** @return array<int, string> */
    private function safeLinks(array $links): array
    {
        return array_values(array_filter(array_map(function (mixed $link): ?string {
            if (! is_string($link) || filter_var($link, FILTER_VALIDATE_URL) === false) {
                return null;
            }

            return in_array(mb_strtolower((string) parse_url($link, PHP_URL_SCHEME)), ['http', 'https'], true)
                ? $link
                : null;
        }, $links)));
    }
}
