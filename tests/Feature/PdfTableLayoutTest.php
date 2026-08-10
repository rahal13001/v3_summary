<?php

namespace Tests\Feature;

use Tests\TestCase;

class PdfTableLayoutTest extends TestCase
{
    public function test_pdf_styles_rich_text_tables_with_proportional_borders(): void
    {
        $view = file_get_contents(resource_path('views/pdf/pdf.blade.php'));

        $this->assertStringContainsString('.report-content table {', $view);
        $this->assertStringContainsString('table-layout: fixed;', $view);
        $this->assertStringContainsString('border: 1px solid #444;', $view);
        $this->assertStringContainsString('overflow-wrap: break-word;', $view);
        $this->assertStringContainsString('.tabledata {', $view);
    }

    public function test_pdf_information_table_stays_plain_without_borders(): void
    {
        $view = file_get_contents(resource_path('views/pdf/pdf.blade.php'));
        preg_match('/\.tabledata\s*\{(.*?)\}/s', $view, $tableStyles);

        $this->assertArrayHasKey(1, $tableStyles);
        $this->assertStringNotContainsString('border:', $tableStyles[1]);
        $this->assertSame(0, preg_match('/\.tabledata\s+td,\s+\.tabledata\s+th\s*\{.*?border:/s', $view));
    }
}
