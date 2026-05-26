<?php

namespace App\Filament\Widgets;

use App\Models\Report;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ReportsOverview extends BaseWidget
{

    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
    
        $baseQuery = fn ($query) => $query->when($startDate, fn (Builder $query) => $query->whereDate('when', '>=', $startDate))
        ->when($endDate, fn (Builder $query) => $query->whereDate('when', '<=', $endDate));
    
        $totalReports = Report::query()->when($startDate || $endDate, $baseQuery)->count();
        $totalMyReports = Report::query()->where('user_id', auth()->id())->when($startDate || $endDate, $baseQuery)->count();
        $totalFollowedReports = Report::query()->whereHas('followers', fn (Builder $query) => $query->where('user_id', auth()->id()))->when($startDate || $endDate, $baseQuery)->count();
    
        return [
            Stat::make('Total 5W1H Semua', $totalReports),
            Stat::make('Total 5W1H-Ku Tulis', $totalMyReports),
            Stat::make('Total 5W1H-Ku Sebagai Pengikut', $totalFollowedReports),
        ];
    }
}
