<?php

namespace App\Filament\Resources\IndicatorResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\IndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIndicators extends ListRecords
{
    protected static string $resource = IndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
