<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use Tests\TestCase;

class DashboardUiContractTest extends TestCase
{
    public function test_dashboard_uses_a_responsive_two_column_layout(): void
    {
        $dashboard = new Dashboard;

        $this->assertSame(['md' => 1, 'xl' => 2], $dashboard->getColumns());
        $this->assertContains('dashboard-page', $dashboard->getPageClasses());
    }

    public function test_dashboard_theme_and_ranking_widgets_have_polished_ui_hooks(): void
    {
        $theme = file_get_contents(resource_path('views/filament/styles/signature-theme.blade.php'));
        $monthView = file_get_contents(resource_path('views/filament/widgets/top-writers-this-month.blade.php'));
        $yearView = file_get_contents(resource_path('views/filament/widgets/top-writers-this-year.blade.php'));

        $this->assertStringContainsString('.dashboard-page', $theme);
        $this->assertStringContainsString('.dashboard-filter-card', $theme);
        $this->assertStringContainsString('dashboard-ranking-header', $monthView);
        $this->assertStringContainsString('dashboard-ranking-header', $yearView);
        $this->assertStringContainsString('.dashboard-ranking-widget > .fi-section', $theme);
        $this->assertStringContainsString('table-layout: fixed', $theme);
        $this->assertStringContainsString('width: 100%', $theme);
        $this->assertStringContainsString('dashboard-ranking-table', $monthView);
        $this->assertStringContainsString('dashboard-ranking-table', $yearView);
        $this->assertStringContainsString('wire:model.live="selectedMonth"', $monthView);
        $this->assertStringContainsString('wire:model.live="selectedYear"', $monthView);
        $this->assertStringContainsString('wire:model.live="selectedYear"', $yearView);
        $this->assertStringNotContainsString('wire:model.live="selectedMonth"', $yearView);
        $this->assertStringNotContainsString('!!!', $monthView . $yearView);
    }
}
