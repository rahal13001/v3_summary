<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\IndicatorResource\Pages\ListIndicators;
use App\Filament\Resources\IndicatorResource\Pages\CreateIndicator;
use App\Filament\Resources\IndicatorResource\Pages;
use App\Filament\Resources\IndicatorResource\RelationManagers;
use App\Models\Indicator;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IndicatorResource extends Resource
{
    protected static ?string $model = Indicator::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?int $navigationSort = 2;

    protected static string | \UnitEnum | null $navigationGroup = 'Admin Area';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_iku')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('nomor_iku')
                ->required()
                ->numeric()
                ->maxLength(255),
            TextInput::make('tahun_iku')
                ->required()
                ->numeric()
                ->maxLength(255),
            Select::make('status_iku')
                ->required()
                ->options([
                    'Aktif' => 'Aktif',
                    'Tidak Aktif' => 'Tidak Aktif',
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_iku')
                    ->searchable(),
                TextColumn::make('nomor_iku')
                    ->searchable()
                    ->sortable(query: function (Builder $query): Builder {
                        return $query
                            ->orderBy('nomor_iku', 'asc');
                    }),
                TextColumn::make('tahun_iku')
                    ->searchable()
                    ->sortable(query: function (Builder $query): Builder {
                        return $query
                            ->orderBy('tahun_iku', 'desc');
                    }),
                TextColumn::make('status_iku')
                    ->searchable()
              
            ])
            ->defaultSort('tahun_iku','desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndicators::route('/'),
            'create' => CreateIndicator::route('/create'),
            // 'edit' => Pages\EditIndicator::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "IKU";
        }
        else
        {
            return "Indicator";
        }
    }
}
