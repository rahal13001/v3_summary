<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\File;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\LabelAlignment;
use BaconQrCode\Common\ErrorCorrectionLevel;

class PdfController extends Controller
{
    public function __invoke(Report $report)
    {


        $qr_report = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data('http://summary.timurbersinar.com/pdf/'.$report->slug)
            ->encoding(new Encoding('UTF-8'))
            ->size(150)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->validateResult(false)
            ->build();


            $qr_lainnya = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
           
                ->data('http://summary.timurbersinar.com/pdf/dokumentasi_lainnya/'.$report->documentation->lainnya)
                ->encoding(new Encoding('UTF-8'))
                ->size(150)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->validateResult(false)
                ->build();


            $qr_st = Builder::create()
                ->writer(new PngWriter())
                ->writerOptions([])
                ->data('http://summary.timurbersinar.com/pdf/dokumentasi_lainnya/'.$report->documentation->st)
                ->encoding(new Encoding('UTF-8'))
                ->size(150)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->validateResult(false)
                ->build();


                //jika mau decode

                // if ($report->kode) {
                //     $data_uri = $report->kode;
                //     $encoded_image = explode(",", $data_uri)[1];
                //     $decoded_image = base64_decode($encoded_image);
                //     $qrdata  = file_put_contents($report->user->name, $decoded_image);
                // } else {
                //     $qrdata = "no data"; // Fallback or alternative data for the QR code
                // }

                //menyimpan file gambar
                // $image = $report->kode;  // your base64 encoded
                // $image = str_replace('data:image/png;base64,', '', $image);
                // $image = str_replace(' ', '+', $image);
                // $imageName = $report->user->name.'.png';
                // File::put(storage_path('/public/signature'). 'signature' . $imageName, base64_decode($image));

                // $qrdata = '<img src='.$imageName.'>';

                //jika ttd dibuat QR

                // if ($report->kode) {
                //     $qrdata = 'http://summary.timurbersinar.com/cek-ttd/'.$report->slug;
                // }
                // else {
                //     $qrdata = "no data"; // Fallback or alternative data for the QR code
                // }
                
                // $qr_ttd = Builder::create()
                //     ->writer(new PngWriter())
                //     ->data($qrdata) // Use the base64-encoded image or fallback data
                //     ->encoding(new Encoding('UTF-8'))
                //     ->size(150)
                //     ->logoPath('../public/img/logokkp.jpg')
                //     ->logoResizeToWidth(30)
                //     ->margin(10)
                //     ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                //     ->validateResult(false)
                //     ->build();
                           


        $q_report = $qr_report->getDataUri();
        $q_lainnya = $qr_lainnya->getDataUri();
        $q_st = $qr_st->getDataUri();
        // $q_ttd = $qr_ttd->getDataUri();

        return Pdf::loadView('pdf.pdf', compact('report', 'q_report', 'q_lainnya', 'q_st'))
            ->stream($report->what. '.pdf');
    }
}
