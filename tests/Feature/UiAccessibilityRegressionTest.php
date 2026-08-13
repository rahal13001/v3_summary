<?php

namespace Tests\Feature;

use App\Providers\Filament\AdminPanelProvider;
use ReflectionClass;
use Tests\TestCase;

class UiAccessibilityRegressionTest extends TestCase
{
    public function test_panel_branding_and_dark_rich_text_links_are_accessible(): void
    {
        $provider = file_get_contents((new ReflectionClass(AdminPanelProvider::class))->getFileName());
        $richText = file_get_contents(resource_path('views/infolists/components/how.blade.php'));
        $theme = file_get_contents(resource_path('views/filament/styles/signature-theme.blade.php'));

        $this->assertStringContainsString('->brandName(fn (): string => app(OrganizationContext::class)->appName())', $provider);
        $this->assertStringContainsString('->favicon(fn (): string => app(OrganizationContext::class)->faviconUrl()', $provider);
        $this->assertStringContainsString(':root.dark .filament-display-how a', $richText);
        $this->assertStringContainsString('color: #60a5fa', $richText);
        $this->assertStringContainsString('.filament-display-how a:focus-visible', $richText);
        $this->assertStringContainsString('color: rgb(75 85 99)', $theme);
        $this->assertStringContainsString('APP_NAME=Summary', file_get_contents(base_path('.env.example')));
    }
}
