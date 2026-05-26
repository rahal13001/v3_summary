<?php

namespace App\Exports;

use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private Collection $reports,
    ) {}

    public function collection(): Collection
    {
        return $this->reports->loadMissing([
            'followers',
            'indicators',
            'teams',
            'user',
        ]);
    }

    public function headings(): array
    {
        return [
            'Penyusun',
            'Pengikut',
            'No. ST',
            'What',
            'IKU',
            'Tim Kerja',
            'Why',
            'When',
            'Tanggal Selesai',
            'Where',
            'Who',
            'How',
            'Penyelenggara',
            'Total Peserta',
            'Persentase Wanita',
        ];
    }

    public function map($report): array
    {
        /** @var Report $report */
        return [
            $report->user?->name,
            $report->followers->pluck('name')->join(', '),
            $report->no_st,
            $report->what,
            $report->indicators->pluck('nama_iku')->join(', '),
            $report->teams->pluck('nama_tim')->join(', '),
            $report->why,
            $this->formatDate($report->when),
            $this->formatDate($report->tanggal_selesai),
            $report->where,
            $report->who,
            $this->plainText($report->how),
            $report->penyelenggara,
            $report->total_peserta,
            "{$report->total_wanita}%",
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getFont()
            ->setName('Arial')
            ->setSize(12);

        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle('1:1')
            ->getFont()
            ->setBold(true);

        $sheet->getStyle('1:1')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    private function formatDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('d-m-Y');
    }

    private function plainText(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return trim(html_entity_decode(strip_tags($value)));
    }
}
