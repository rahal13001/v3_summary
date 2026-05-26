<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Executor;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class AllOrdersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDisposisi = Executor::count(); //total disposisi
        $disposisiSelesai = Executor::where('status', 1)->count(); //total disposisi selesai
        $disposisiBelumSelesai = Executor::where('status', 0)->count(); //total disposisi belum selesai

        return [
            Stat::make('Total Disposisi ke Pegawai', $totalDisposisi),
            Stat::make('Disposisi Diselesaikan Pegawai', $disposisiSelesai),
            Stat::make('Disposisi Belum Diselesaikan Pegawai', $disposisiBelumSelesai),
        ];
    }
}
