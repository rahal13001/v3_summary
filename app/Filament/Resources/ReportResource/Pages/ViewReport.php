<?php

namespace App\Filament\Resources\ReportResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ReportResource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    public function defaultInfolist(Schema $schema): Schema
    {
        return parent::defaultInfolist($schema)->columns(1);
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()->id == $this->record->user_id || $this->record->followers->find(auth()->user()->id) || auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin')) {
            return [
                EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
              
                Action::make('Export PDF')
                    ->label('Export PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->url(fn ($record) => route('pdf', $record->slug), '_blank')
                    ->button()
                    ->color('info')
                    ->labeledFrom('md'),

                DeleteAction::make(),
            ];
        } else {
            return [
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
}
