<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square'),
            Action::make('Export PDF')
                ->label('Export PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->url(fn ($record) => route('pdf', $record->slug), '_blank')
                ->button()
                ->color('danger')
                ->labeledFrom('md')
            
        ];
    }
}
