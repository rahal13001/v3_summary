<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TopWritersThisMonth extends Widget
{
    protected static ?int $sort = 5;

    public int $selectedMonth;

    public int $selectedYear;

    protected string $view = 'filament.widgets.top-writers-this-month';

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function getMonthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function getYearOptions(): array
    {
        return collect(range(now()->year - 5, now()->year))
            ->push($this->selectedYear)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function getRankingUsers(): Collection
    {
        return DB::table('users')
            ->join('reports', 'reports.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('COUNT(reports.id) as reports_count'))
            ->whereYear('reports.when', $this->selectedYear)
            ->whereMonth('reports.when', $this->selectedMonth)
            ->whereNull('reports.deleted_at')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('reports_count')
            ->orderBy('users.name')
            ->limit(5)
            ->get();
    }

    protected function getViewData(): array
    {
        return [
            'users' => $this->getRankingUsers(),
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
            'monthOptions' => $this->getMonthOptions(),
            'yearOptions' => $this->getYearOptions(),
        ];
    }
}
