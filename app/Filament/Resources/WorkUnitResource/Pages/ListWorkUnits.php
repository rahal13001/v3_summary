<?php

namespace App\Filament\Resources\WorkUnitResource\Pages;

use App\Filament\Resources\WorkUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkUnits extends ListRecords
{
    protected static string $resource = WorkUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
