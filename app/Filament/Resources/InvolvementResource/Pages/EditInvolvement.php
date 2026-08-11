<?php

namespace App\Filament\Resources\InvolvementResource\Pages;

use App\Filament\Resources\InvolvementResource;
use App\Models\Involvement;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvolvement extends EditRecord
{
    protected static string $resource = InvolvementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn (Involvement $record): bool => $record->reports()->exists())
                ->tooltip('Keterlibatan yang sudah dipakai laporan tidak dapat dihapus.'),
        ];
    }
}
