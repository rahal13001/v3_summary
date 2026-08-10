<?php

namespace Tests\Feature;

use App\Filament\Widgets\TopWritersThisMonth;
use App\Filament\Widgets\TopWritersThisYear;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWriterFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_month_writer_filter_uses_both_selected_month_and_year(): void
    {
        $januaryWriter = User::factory()->create(['name' => 'January Writer']);
        $februaryWriter = User::factory()->create(['name' => 'February Writer']);

        $this->createReport($januaryWriter, '2026-01-15');
        $this->createReport($februaryWriter, '2026-02-15');
        $this->createReport($januaryWriter, '2025-01-15');

        $widget = new TopWritersThisMonth;
        $widget->mount();
        $widget->selectedMonth = 2;
        $widget->selectedYear = 2026;

        $this->assertSame(['February Writer'], $widget->getRankingUsers()->pluck('name')->all());
    }

    public function test_year_writer_filter_uses_only_the_selected_year(): void
    {
        $currentYearWriter = User::factory()->create(['name' => 'Current Year Writer']);
        $previousYearWriter = User::factory()->create(['name' => 'Previous Year Writer']);

        $this->createReport($currentYearWriter, '2026-01-15');
        $this->createReport($currentYearWriter, '2026-08-15');
        $this->createReport($previousYearWriter, '2025-08-15');

        $widget = new TopWritersThisYear;
        $widget->mount();
        $widget->selectedYear = 2026;

        $this->assertSame(['Current Year Writer'], $widget->getRankingUsers()->pluck('name')->all());
    }

    private function createReport(User $user, string $date): void
    {
        Report::withoutEvents(function () use ($user, $date): void {
            Report::query()->create([
                'user_id' => $user->id,
                'slug' => $user->id . '-' . $date,
                'no_st' => 'ST-' . $user->id . '-' . $date,
                'what' => 'Kegiatan',
                'why' => 'Kebutuhan',
                'when' => $date,
                'tanggal_selesai' => $date,
                'where' => 'Sorong',
                'who' => 'Tim',
                'how' => 'Pelaksanaan',
                'penyelenggara' => 'Summary',
                'total_peserta' => '1',
                'total_wanita' => '0',
                'kode' => 'kode-' . $user->id,
            ]);
        });
    }
}
