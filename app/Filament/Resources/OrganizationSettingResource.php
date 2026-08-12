<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationSettingResource\Pages\CreateOrganizationSetting;
use App\Filament\Resources\OrganizationSettingResource\Pages\EditOrganizationSetting;
use App\Filament\Resources\OrganizationSettingResource\Pages\ListOrganizationSettings;
use App\Models\OrganizationSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationSettingResource extends Resource
{
    protected static ?string $model = OrganizationSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin Area';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Organisasi')
                    ->description('Identitas publik deployment ini. Konfigurasi teknis tetap dikelola melalui .env.')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama lengkap')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('short_name')
                            ->label('Nama singkat')
                            ->required()
                            ->maxLength(100),
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->directory('organisasi')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Toggle::make('monev_enabled')
                            ->label('Aktifkan Evaluasi Monev')
                            ->helperText('Nonaktif secara default. Aktifkan hanya pada deployment yang memakai modul Monev.')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Organisasi'),
                TextColumn::make('short_name')->label('Nama singkat'),
                IconColumn::make('monev_enabled')->label('Monev')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return parent::canCreate() && ! OrganizationSetting::query()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationSettings::route('/'),
            'create' => CreateOrganizationSetting::route('/create'),
            'edit' => EditOrganizationSetting::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Pengaturan Organisasi';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengaturan Organisasi';
    }
}
