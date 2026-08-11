<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function __invoke(Report $report)
    {
        $report->loadMissing([
            'documentation',
            'followers',
            'indicators',
            'involvement',
            'user',
            'workUnits',
        ]);

        $documentation = $report->documentation;
        $publicDisk = Storage::disk('public');
        $documentationFiles = [];

        foreach (['dokumentasi1', 'dokumentasi2', 'dokumentasi3'] as $field) {
            $path = ltrim(trim((string) ($documentation?->{$field} ?? '')), '/');

            if (blank($path)) {
                continue;
            }

            try {
                if ($publicDisk->exists($path)) {
                    $documentationFiles[$field] = $publicDisk->path($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        $qrReport = Builder::create()
            ->writer(new PngWriter)
            ->writerOptions([])
            ->data(route('pdf', ['report' => $report->getRouteKey()]))
            ->encoding(new Encoding('UTF-8'))
            ->size(150)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->validateResult(false)
            ->build()
            ->getDataUri();

        $buildFileQr = static function (?string $path) use ($publicDisk): ?string {
            $path = ltrim(trim($path ?? ''), '/');

            if (blank($path)) {
                return null;
            }

            try {
                if (! $publicDisk->exists($path)) {
                    return null;
                }

                return Builder::create()
                    ->writer(new PngWriter)
                    ->writerOptions([])
                    ->data($publicDisk->url($path))
                    ->encoding(new Encoding('UTF-8'))
                    ->size(150)
                    ->margin(10)
                    ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                    ->validateResult(false)
                    ->build()
                    ->getDataUri();
            } catch (\Throwable) {
                return null;
            }
        };

        $qLainnya = $buildFileQr($documentation?->lainnya);
        $qSt = $buildFileQr($documentation?->st);

        return Pdf::loadView('pdf.pdf', [
            'report' => $report,
            'q_report' => $qrReport,
            'q_lainnya' => $qLainnya,
            'q_st' => $qSt,
            'documentationFiles' => $documentationFiles,
        ])->stream($report->what.'.pdf');
    }
}
