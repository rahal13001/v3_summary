<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicStorageController extends Controller
{
    /** @var array<string, array<string>> */
    private const ALLOWED_MIME_TYPES = [
        'doc' => ['application/msword', 'application/x-ole-storage'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'gif' => ['image/gif'],
        'jpeg' => ['image/jpeg'],
        'jpg' => ['image/jpeg'],
        'pdf' => ['application/pdf'],
        'png' => ['image/png'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/x-ole-storage'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'webp' => ['image/webp'],
        'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
    ];

    public function __invoke(string $path): StreamedResponse
    {
        abort_if(str_contains($path, '..') || str_contains($path, '\\'), 404);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        abort_unless(array_key_exists($extension, self::ALLOWED_MIME_TYPES), 404);

        try {
            $disk = Storage::disk('public');
            $exists = $disk->exists($path);
            $mimeType = $exists ? $disk->mimeType($path) : null;
        } catch (\Throwable) {
            $exists = false;
            $mimeType = null;
        }

        abort_unless($exists, 404);
        abort_unless(is_string($mimeType) && in_array(strtolower($mimeType), self::ALLOWED_MIME_TYPES[$extension], true), 404);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
        ];

        if (! in_array($extension, ['gif', 'jpeg', 'jpg', 'pdf', 'png', 'webp'], true)) {
            return $disk->download($path, headers: $headers);
        }

        return $disk->response($path, headers: $headers);
    }
}
