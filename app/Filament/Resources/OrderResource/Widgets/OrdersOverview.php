<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use App\Models\Executor;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrdersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDisposisi = Executor::where('user_id', Auth::user()->id)->count(); //total disposisi
        $disposisiSelesai = Executor::where('user_id', Auth::user()->id)->where('status', 1)->count(); //total disposisi selesai
        $disposisiBelumSelesai = Executor::where('user_id', Auth::user()->id)->where('status',0)->count(); //total disposisi belum selesai

        return [
            Stat::make('Total Disposisiku', $totalDisposisi),
            Stat::make('Disposisiku Selesai', $disposisiSelesai),
            Stat::make('Disposisiku Belum Selesai', $disposisiBelumSelesai),
            
        ];
    }
}
