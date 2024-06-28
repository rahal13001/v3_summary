<?php

namespace App\Filament\Widgets;

use App\Models\Team;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ReportsPerTeamChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = '5W1H Per Tim Kerja';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $teams = Team::query()->where('status_tim', 'aktif')
        ->withCount([
            'reports' => function ($query) use ($startDate, $endDate) {
                $query->when($startDate, fn ($query) => $query->whereDate('when', '>=', $startDate))
                    ->when($endDate, fn ($query) => $query->whereDate('when', '<=', $endDate));
            },
        ])
        ->get();

        return [
            'labels' => $teams->pluck('nomor_tim')->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan',
                    'data' => $teams->pluck('reports_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 0.6)', // border color
                        'rgba(54, 162, 235, 0.6)', // border color
                        'rgba(255, 206, 86, 0.6)', // border color
                        'rgba(75, 192, 192, 0.6)', // border color
                        'rgba(153, 102, 255, 0.6)', // border color
                        'rgba(255, 159, 64, 0.6)', // border color
                        'rgba(255, 99, 132, 0.6)', // border color
                        'rgba(54, 162, 235, 0.6)', // border color
                        'rgba(255, 206, 86, 0.6)', // border color
                        'rgba(75, 192, 192, 0.6)', // border color
                        'rgba(153, 102, 255, 0.6)', // border color
                        'rgba(255, 159, 64, 0.6)', // border color
                        'rgba(255, 99, 132, 0.6)', // border color
                        'rgba(54, 162, 235, 0.6)', // border color
                        'rgba(255, 206, 86, 0.6)', // border color
                        'rgba(75, 192, 192, 0.6)', // border color
                        'rgba(153, 102, 255, 0.6)', // border color
                        'rgba(255, 159, 64, 0.6)', // border color
                        'rgba(255, 99, 132, 0.6)', // border color
                        'rgba(54, 162, 235, 0.6)', // border color
                        'rgba(255, 206, 86, 0.6)', // border color
                        'rgba(75, 192, 192, 0.6)', // border color
                        'rgba(153, 102, 255, 0.6)', // border color
                        'rgba(255, 159, 64, 0.6)', // border color
                    ],
                    'borderWidth' => 1,
                ]
            ]

        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
