<?php

namespace Tests\Feature;

use Tests\TestCase;

class RichTextSanitizationTest extends TestCase
{
    public function test_report_rich_text_is_sanitized_before_rendering(): void
    {
        $html = view('infolists.components.how', [
            'state' => '<p>Isi aman</p><script>alert(1)</script><img src="x" onerror="alert(2)">',
        ])->render();

        $this->assertStringContainsString('<p>Isi aman</p>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function test_how_tables_adapt_to_light_and_dark_theme(): void
    {
        $html = view('infolists.components.how', [
            'state' => '<table><tr><th>Header</th></tr><tr><td>Isi</td></tr></table>',
        ])->render();

        $this->assertStringContainsString(':root.dark .filament-display-how', $html);
        $this->assertStringContainsString('--how-table-background: transparent', $html);
        $this->assertStringContainsString('--how-table-text: #ffffff', $html);
        $this->assertStringContainsString('background-color: var(--how-table-background) !important', $html);
        $this->assertStringContainsString('color: var(--how-table-text) !important', $html);
    }
}
