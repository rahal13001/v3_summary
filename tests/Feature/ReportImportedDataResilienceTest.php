<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportImportedDataResilienceTest extends TestCase
{
    public function test_report_pdf_handles_missing_documentation_and_missing_local_files(): void
    {
        $controller = file_get_contents(base_path('app/Http/Controllers/PdfController.php'));
        $view = file_get_contents(resource_path('views/pdf/pdf.blade.php'));

        $this->assertStringContainsString('$documentation?->st', $controller);
        $this->assertStringContainsString("Storage::disk('public')", $controller);
        $this->assertStringContainsString('->exists', $controller);
        $this->assertStringContainsString('$documentationFiles', $controller);
        $this->assertStringContainsString("\$documentationFiles['dokumentasi1']", $view);
        $this->assertStringNotContainsString('$report->documentation->dokumentasi1', $view);

        $storageController = file_get_contents(base_path('app/Http/Controllers/PublicStorageController.php'));
        $this->assertStringContainsString('catch (\\Throwable)', $storageController);

        $legacyController = file_get_contents(base_path('app/Http/Controllers/Summary/ReportController.php'));
        $this->assertStringContainsString("Storage::disk('public')->exists", $legacyController);
        $this->assertStringContainsString("route('public-storage.show'", $legacyController);
    }
}
