<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Models\Report;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        
        return [
            '5W1H Sebagai Penulis' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query)
                    => $query->where('user_id', auth()->id())
                    ),
            '5W1H Sebagai Pengikut' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query)
                    => $query->whereHas('followers', fn (Builder $query) => $query->where('id', auth()->id()))
                    ),
            'Semua 5W1H' => Tab::make('Semua 5W1H'),
        ];
    }
}
