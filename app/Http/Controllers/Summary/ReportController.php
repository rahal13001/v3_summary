<?php

namespace App\Http\Controllers\Summary;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function viewlainnya(string $lainnya_upload): Response
    {
        return $this->renderStoredFile('lainnya', $lainnya_upload, 'lihat.lihatlainnya');
    }

    public function viewst(string $st_upload): Response
    {
        return $this->renderStoredFile('st', $st_upload, 'lihat.lihatst');
    }

    private function renderStoredFile(string $directory, string $file, string $view): Response
    {
        $path = trim($file, '/');
        $path = str_starts_with($path, $directory.'/') ? $path : $directory.'/'.$path;

        try {
            $exists = Storage::disk('public')->exists($path);
        } catch (\Throwable) {
            $exists = false;
        }

        abort_unless($exists, 404);

        return response()->view($view, [
            'url' => route('public-storage.show', ['path' => $path]),
        ]);
    }
}
