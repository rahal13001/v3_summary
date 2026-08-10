<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\TeamResource\Pages\ListTeams;
use App\Filament\Resources\TeamResource\Pages\CreateTeam;
use App\Filament\Resources\TeamResource\Pages\EditTeam;
use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $title = 'Laporan 5W1H';
    protected static ?int $navigationSort = 3;

    protected static string | \UnitEnum | null $navigationGroup = 'Admin Area';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_tim')
                ->required()
                ->maxLength(255),
            TextInput::make('nomor_tim')
                ->required()
                ->maxLength(255),
            Select::make('status_tim')
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
                TextColumn::make('nama_tim')
                ->searchable(),
                TextColumn::make('status_tim')
                    ->searchable(),
                TextColumn::make('nomor_tim')
                    ->searchable()
            ])
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
            'index' => ListTeams::route('/'),
            'create' => CreateTeam::route('/create'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Tim Kerja";
        }
        else
        {
            return "Team";
        }
    }
}
