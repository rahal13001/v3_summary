<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IndicatorResource\Pages;
use App\Filament\Resources\IndicatorResource\RelationManagers;
use App\Models\Indicator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IndicatorResource extends Resource
{
    protected static ?string $model = Indicator::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Admin Area';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_iku')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('nomor_iku')
                ->required()
                ->numeric()
                ->maxLength(255),
            Forms\Components\TextInput::make('tahun_iku')
                ->required()
                ->numeric()
                ->maxLength(255),
            Forms\Components\Select::make('status_iku')
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
                Tables\Columns\TextColumn::make('nama_iku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_iku')
                    ->searchable()
                    ->sortable(query: function (Builder $query): Builder {
                        return $query
                            ->orderBy('nomor_iku', 'asc');
                    }),
                Tables\Columns\TextColumn::make('tahun_iku')
                    ->searchable()
                    ->sortable(query: function (Builder $query): Builder {
                        return $query
                            ->orderBy('tahun_iku', 'desc');
                    }),
                Tables\Columns\TextColumn::make('status_iku')
                    ->searchable()
              
            ])
            ->defaultSort('tahun_iku','desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListIndicators::route('/'),
            'create' => Pages\CreateIndicator::route('/create'),
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
