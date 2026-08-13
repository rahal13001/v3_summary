<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvolvementResource\Pages\CreateInvolvement;
use App\Filament\Resources\InvolvementResource\Pages\EditInvolvement;
use App\Filament\Resources\InvolvementResource\Pages\ListInvolvements;
use App\Models\Involvement;
use App\Services\OrganizationContext;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvolvementResource extends Resource
{
    protected static ?string $model = Involvement::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin Area';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Keterlibatan')
                    ->description('Atur jenis peran '.app(OrganizationContext::class)->shortName().' dalam kegiatan.')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Keterlibatan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options(Involvement::statusOptions())
                            ->default(Involvement::STATUS_ACTIVE)
                            ->required()
                            ->native(false),
                        Toggle::make('is_lprl_organizer')
                            ->label('Organisasi sebagai penyelenggara/internal')
                            ->helperText('Jika aktif, pengisian Penyelenggara mengikuti kebijakan pada Pengaturan Organisasi.')
                            ->default(false)
                            ->inline(false),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Keterlibatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => Involvement::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === Involvement::STATUS_ACTIVE ? 'success' : 'gray')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_lprl_organizer')
                    ->label('Organisasi Penyelenggara/Internal')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('status')
                    ->options(Involvement::statusOptions()),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvolvements::route('/'),
            'create' => CreateInvolvement::route('/create'),
            'edit' => EditInvolvement::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Keterlibatan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Keterlibatan';
    }
}
