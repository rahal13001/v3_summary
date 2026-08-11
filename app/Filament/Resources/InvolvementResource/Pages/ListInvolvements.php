<?php

namespace App\Filament\Resources\InvolvementResource\Pages;

use App\Filament\Resources\InvolvementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvolvements extends ListRecords
{
    protected static string $resource = InvolvementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
