<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\Order;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Components\Tab;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Widgets\OrdersOverview;
use App\Filament\Resources\OrderResource\Widgets\AllOrdersOverview;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        
            return [
                OrdersOverview::class,
            ];
        
        
    }

    protected function getFooterWidgets(): array
    {
        $user = Auth::user()->can('create', Order::class);

        if ($user == true) {
            return [
                AllOrdersOverview::class,
            ];
        }

        return [];
        
    }

    public function getTabs(): array
    {
        $is_user = Auth::user()->can('create', Order::class);

        $tabs = [];

        if ($is_user == true) {
            $tabs['Semua Disposisi'] = Tab::make('Semua Disposisi');
            $tabs['Disposisi Selesai'] = Tab::make()
            ->modifyQueryUsing(fn (Builder $query)
                => $query->whereDoesntHave('executor', fn (Builder $query)
                    => $query->where('status', 0))
            );
            $tabs['Disposisi Belum Selesai'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query)
                    => $query->whereHas('executor', fn (Builder $query)
                        => $query->where('status', 0))
           );
        }
            $tabs['Disposisi Saya'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query)
                    => $query->whereHas('executor', fn (Builder $query)
                        => $query->where('user_id', Auth::user()->id))
                    );
            $tabs['Disposisi Saya Selesai'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query)
                    => $query->whereHas('executor', fn (Builder $query)
                        => $query->where('user_id', Auth::user()->id)->where('status', 1))
                    );
            $tabs['Disposisi Saya Belum Selesai'] = Tab::make()
                ->modifyQueryUsing(fn (Builder $query)
                    => $query->whereHas('executor', fn (Builder $query)
                        => $query->where('user_id', Auth::user()->id)->where('status', 0))
                    );
       
         return $tabs;

    }
}
