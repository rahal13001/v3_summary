<?php

namespace App\Filament\Widgets;

use App\Models\Indicator;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ReportsPerIndicatorChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?string $heading = '5W1H Per IKU';

    protected static ?int $sort = 3;

    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $indicators = Indicator::query()->where('status_iku', 'aktif')
            ->withCount([
                'reports' => function ($query) use ($startDate, $endDate) {
                    $query->when($startDate, fn ($query) => $query->whereDate('when', '>=', $startDate))
                        ->when($endDate, fn ($query) => $query->whereDate('when', '<=', $endDate))->orderBy('nomor_iku');
                },
            ])
            ->get();

            $dataset = $indicators->map(function ($indicator) {
                return [
                    'nama_iku' => $indicator->nama_iku,
                    'nomor_iku' => $indicator->nomor_iku,
                    'reports_count' => $indicator->reports_count,
                ];
            });

        return [
            'labels' => $indicators->pluck('nomor_iku')->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan',
                    'data' => $indicators->pluck('reports_count')->toArray(),
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
                ],
            ],
        ];
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

    protected function getType(): string
    {
        return 'bar';
    }
}
