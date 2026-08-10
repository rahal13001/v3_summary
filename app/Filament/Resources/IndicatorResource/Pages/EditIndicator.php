<?php

namespace App\Filament\Resources\IndicatorResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\IndicatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIndicator extends EditRecord
{
    protected static string $resource = IndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
