<?php

namespace App\Filament\Resources\WorkUnitResource\Pages;

use App\Filament\Resources\WorkUnitResource;
use App\Models\WorkUnit;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkUnit extends EditRecord
{
    protected static string $resource = WorkUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn (WorkUnit $record): bool => $record->reports()->exists())
                ->tooltip('Unit Kerja yang sudah dipakai laporan tidak dapat dihapus.'),
        ];
    }
}
