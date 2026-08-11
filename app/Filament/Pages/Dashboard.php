<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    /**
     * Keep charts and rankings side by side on wide screens without squeezing
     * the content on tablets and smaller devices.
     *
     * @return array<string, int>
     */
    public function getColumns(): int | array
    {
        return [
            'md' => 1,
            'xl' => 2,
        ];
    }

    public function getPageClasses(): array
    {
        return ['dashboard-page'];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter periode')
                    ->description('Pilih rentang tanggal untuk menyaring angka ringkasan dan grafik.')
                    ->icon('heroicon-o-calendar-days')
                    ->extraAttributes(['class' => 'dashboard-filter-card'])
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal mulai')
                            ->placeholder('Pilih tanggal mulai')
                            ->native(false),
                        DatePicker::make('endDate')
                            ->label('Tanggal selesai')
                            ->placeholder('Pilih tanggal selesai')
                            ->native(false),
                    ])
                    ->columns([
                        'md' => 2,
                        'xl' => 2,
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
