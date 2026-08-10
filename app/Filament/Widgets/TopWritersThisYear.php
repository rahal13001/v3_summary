<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TopWritersThisYear extends Widget
{
    protected static ?int $sort = 6;

    public int $selectedYear;

    protected string $view = 'filament.widgets.top-writers-this-year';

    public function mount(): void
    {
        $this->selectedYear = now()->year;
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
            'selectedYear' => $this->selectedYear,
            'yearOptions' => $this->getYearOptions(),
        ];
    }
}
