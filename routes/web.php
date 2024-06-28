<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\SignatureverificationController;
use App\Http\Controllers\Summary\ReportController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/pdf/{report}', PdfController::class)->name('pdf');

Route::get('/cek-ttd/{report}', SignatureverificationController::class)->name('signatureverification');


// require __DIR__.'/auth.php';

Route::get('lihat_lainnya/{lainnya_upload}',[ReportController::class, 'viewlainnya'])->name('view_lainnya');

//Lihat dokumentasi st
Route::get('lihat_st/{st_upload}',[ReportController::class, 'viewst'])->name('view_st');