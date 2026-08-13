<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationSettingResource\Pages\CreateOrganizationSetting;
use App\Filament\Resources\OrganizationSettingResource\Pages\EditOrganizationSetting;
use App\Filament\Resources\OrganizationSettingResource\Pages\ListOrganizationSettings;
use App\Models\OrganizationSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
                Section::make('Branding Aplikasi')
                    ->description('Nama dan aset visual yang tampil pada panel deployment ini.')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('app_name')
                            ->label('Nama web/aplikasi')
                            ->placeholder('Contoh: Summary atau Teripang')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                        FileUpload::make('logo_path')
                            ->label('Logo aplikasi')
                            ->helperText('JPEG, PNG, atau WebP. Maksimal 2 MB.')
                            ->disk('public')
                            ->directory('organisasi')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->helperText('Gunakan gambar persegi JPEG, PNG, atau WebP. Maksimal 2 MB.')
                            ->disk('public')
                            ->directory('organisasi')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048),
                    ]),
                Section::make('Identitas Organisasi')
                    ->description('Identitas organisasi yang mengoperasikan deployment ini.')
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
                Section::make('Perilaku Penyelenggara')
                    ->description('Berlaku saat Keterlibatan ditandai sebagai organisasi penyelenggara/internal.')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        TextInput::make('organizer_name')
                            ->label('Nama default Penyelenggara')
                            ->helperText('Jika kosong, sistem memakai Nama singkat organisasi.')
                            ->maxLength(255),
                        Select::make('organizer_input_mode')
                            ->label('Cara pengisian Penyelenggara')
                            ->options(OrganizationSetting::organizerInputModeOptions())
                            ->default(OrganizationSetting::ORGANIZER_MODE_LOCKED)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Organisasi'),
                TextColumn::make('app_name')->label('Nama aplikasi'),
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
