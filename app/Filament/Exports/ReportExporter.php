<?php

namespace App\Filament\Exports;

use App\Models\Report;
use Filament\Actions\Exports\Exporter;
use OpenSpout\Common\Entity\Style\Style;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;

class ReportExporter extends Exporter
{
    protected static ?string $model = Report::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')->label('Penyusun'),
            ExportColumn::make('followers.name')->label('Pengikut'),
            ExportColumn::make('no_st')->label('No. ST'),
            ExportColumn::make('what')->label('What'),
            ExportColumn::make('indicators.nama_iku')->label('IKU'),
            ExportColumn::make('teams.nama_tim')->label('Tim Kerja'),
            ExportColumn::make('why')->label('Why'),
            ExportColumn::make('when')->label('When'),
            ExportColumn::make('tanggal_selesai')->label('Tanggal Selesai'),
            ExportColumn::make('where')->label('Where'),
            ExportColumn::make('who')->label('Who'),
            ExportColumn::make('how')->label('How'),
            ExportColumn::make('penyelenggara')->label('Penyelenggara'),
            ExportColumn::make('total_peserta')->label('Total Peserta'),
            ExportColumn::make('total_wanita')->label('Persentase Wanita')->suffix('%'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your report export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getXlsxCellStyle(): ?Style
    {
        return (new Style())
            ->setFontSize(12)
            ->setFontName('Arial')
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText(true)
            ->setFormat('d-m-yy h:mm');
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontName('Arial')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }
}
