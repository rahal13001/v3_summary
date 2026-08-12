<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkUnitResource\Pages\CreateWorkUnit;
use App\Filament\Resources\WorkUnitResource\Pages\EditWorkUnit;
use App\Filament\Resources\WorkUnitResource\Pages\ListWorkUnits;
use App\Filament\Resources\WorkUnitResource\RelationManagers\CoordinatorAssignmentsRelationManager;
use App\Models\WorkUnit;
use App\Services\OrganizationContext;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class WorkUnitResource extends Resource
{
    protected static ?string $model = WorkUnit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin Area';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Unit Kerja')
                    ->description('Kelola kantor di bawah naungan '.app(OrganizationContext::class)->shortName().'.')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Unit Kerja')
                            ->required()
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule
                                    ->where('unit', $get('unit')),
                            )
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('unit')
                            ->label('Jenis Unit')
                            ->options(WorkUnit::unitOptions())
                            ->required()
                            ->native(false),
                        Select::make('status')
                            ->label('Status')
                            ->options(WorkUnit::statusOptions())
                            ->default(WorkUnit::STATUS_ACTIVE)
                            ->required()
                            ->native(false),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Unit Kerja')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Jenis Unit')
                    ->formatStateUsing(fn (string $state): string => WorkUnit::unitOptions()[$state] ?? $state)
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => WorkUnit::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === WorkUnit::STATUS_ACTIVE ? 'success' : 'gray')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('status')
                    ->options(WorkUnit::statusOptions()),
                SelectFilter::make('unit')
                    ->label('Jenis Unit')
                    ->options(WorkUnit::unitOptions()),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkUnits::route('/'),
            'create' => CreateWorkUnit::route('/create'),
            'edit' => EditWorkUnit::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            CoordinatorAssignmentsRelationManager::class,
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Unit Kerja';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Unit Kerja';
    }
}
