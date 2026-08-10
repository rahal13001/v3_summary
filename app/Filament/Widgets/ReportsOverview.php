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

    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;
    
        $baseQuery = fn ($query) => $query->when($startDate, fn (Builder $query) => $query->whereDate('when', '>=', $startDate))
        ->when($endDate, fn (Builder $query) => $query->whereDate('when', '<=', $endDate));
    
        $totalReports = Report::query()->when($startDate || $endDate, $baseQuery)->count();
        $totalMyReports = Report::query()->where('user_id', auth()->id())->when($startDate || $endDate, $baseQuery)->count();
        $totalFollowedReports = Report::query()->whereHas('followers', fn (Builder $query) => $query->where('user_id', auth()->id()))->when($startDate || $endDate, $baseQuery)->count();
    
        return [
            Stat::make('Total laporan', $totalReports)
                ->description('Semua laporan dalam periode')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Laporan saya', $totalMyReports)
                ->description('Laporan yang saya tulis')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('success'),
            Stat::make('Diikuti', $totalFollowedReports)
                ->description('Laporan yang saya ikuti')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
