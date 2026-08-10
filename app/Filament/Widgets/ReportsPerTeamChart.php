<?php

namespace App\Filament\Widgets;

use App\Models\Team;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ReportsPerTeamChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected ?string $heading = '5W1H Per Tim Kerja';

    protected ?string $description = 'Distribusi laporan pada tim kerja yang aktif.';

    protected ?string $maxHeight = '290px';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

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
                    'borderRadius' => 8,
                    'borderSkipped' => false,
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
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
