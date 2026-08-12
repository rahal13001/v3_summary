<?php

namespace App\Filament\Resources;

use App\Exports\ReportsExport;
use App\Filament\Forms\Components\SignaturePad;
use App\Filament\Resources\ReportResource\Pages\CreateReport;
use App\Filament\Resources\ReportResource\Pages\EditReport;
use App\Filament\Resources\ReportResource\Pages\ListReports;
use App\Filament\Resources\ReportResource\Pages\ViewReport;
use App\Filament\Resources\ReportResource\RelationManagers\EvaluationsRelationManager;
use App\Models\Indicator;
use App\Models\Involvement;
use App\Models\Report;
use App\Models\Team;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\OrganizationContext;
use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'laporan-5w1h';

    protected static string|\UnitEnum|null $navigationGroup = 'Executive Summary';

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                GazeBanner::make()
                    ->pollTimer(10)
                    ->lock()
                    ->hideOnCreate()
                    ->canTakeControl(),
                Section::make('Penyusun Laporan')
                    ->description('Tentukan penyusun, pengikut, dan nomor surat tugas laporan.')
                    ->icon('heroicon-o-user-group')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Select::make('user_id')
                            ->required()
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('status', 1),
                            )
                            ->preload()
                            ->default(Auth::user()->id)
                            ->label('Penyusun')
                            ->searchable()
                            ->searchPrompt('Cari nama pegawai '.app(OrganizationContext::class)->shortName()),
                        Select::make('followers')
                            ->nullable()
                            ->relationship(
                                name: 'followers',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('status', 1),
                            )
                            ->preload()
                            ->label('Pengikut')
                            ->searchable()
                            ->searchPrompt('Cari nama pegawai '.app(OrganizationContext::class)->shortName())
                            ->multiple(),
                        TextInput::make('no_st')
                            ->maxLength(255)
                            ->nullable()
                            ->label('Nomor surat tugas')
                            ->columnSpanFull(),
                    ]),
                Section::make('Ringkasan Kegiatan')
                    ->description('Catat tujuan, jadwal, lokasi, indikator, dan tim kerja kegiatan.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Textarea::make('what')
                            ->required()
                            ->label('What')
                            ->columnSpanFull(),
                        Textarea::make('why')
                            ->required()
                            ->label('Why')
                            ->columnSpanFull(),
                        DatePicker::make('when')
                            ->required()
                            ->label('Tanggal mulai'),
                        DatePicker::make('tanggal_selesai')
                            ->required()
                            ->label('Tanggal selesai'),
                        Textarea::make('where')
                            ->required()
                            ->label('Where')
                            ->columnSpanFull(),
                        Select::make('indicators')
                            ->relationship(
                                name: 'indicators',
                                titleAttribute: 'nama_iku',
                                modifyQueryUsing: fn ($query) => $query->where('status_iku', 'aktif'),
                            )
                            ->label('IKU')
                            ->preload()
                            ->multiple()
                            ->searchable()
                            ->searchPrompt('Cari indikator')
                            ->columnSpanFull(),
                        Select::make('teams')
                            ->relationship('teams', 'nama_tim')
                            ->label('Tim Kerja')
                            ->options(Team::where('status_tim', 'aktif')->pluck('nama_tim', 'id'))
                            ->preload()
                            ->searchable()
                            ->searchPrompt('Cari Tim Kerja')
                            ->multiple()
                            ->columnSpanFull(),
                        Select::make('workUnits')
                            ->relationship(
                                name: 'workUnits',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, ?Report $record): Builder {
                                    $selectedIds = $record?->workUnits()->pluck('work_units.id')->all() ?? [];

                                    return $query->where(function (Builder $query) use ($selectedIds): void {
                                        $query->where('status', WorkUnit::STATUS_ACTIVE);

                                        if ($selectedIds !== []) {
                                            $query->orWhereIn('work_units.id', $selectedIds);
                                        }
                                    });
                                },
                            )
                            ->label('Unit Kerja')
                            ->preload()
                            ->searchable()
                            ->searchPrompt('Cari Unit Kerja')
                            ->multiple()
                            ->minItems(1)
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Pelaksanaan dan Peserta')
                    ->description('Lengkapi pelaksana, penyelenggara, peserta, dan uraian pelaksanaan.')
                    ->icon('heroicon-o-users')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Textarea::make('who')
                            ->required()
                            ->label('Who')
                            ->columnSpanFull(),
                        Select::make('involvement_id')
                            ->relationship(
                                name: 'involvement',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, ?Report $record): Builder {
                                    return $query->where(function (Builder $query) use ($record): void {
                                        $query->where('status', Involvement::STATUS_ACTIVE);

                                        if ($record?->involvement_id) {
                                            $query->orWhereKey($record->involvement_id);
                                        }
                                    });
                                },
                            )
                            ->label('Keterlibatan')
                            ->preload()
                            ->searchable()
                            ->live()
                            ->afterStateHydrated(function (Set $set, $state): void {
                                $involvement = filled($state)
                                    ? Involvement::query()->find($state)
                                    : null;

                                if ($involvement?->organizerName()) {
                                    $set('penyelenggara', $involvement->organizerName());
                                }
                            })
                            ->afterStateUpdated(function (Set $set, $state): void {
                                $involvement = filled($state)
                                    ? Involvement::query()->find($state)
                                    : null;

                                $set('penyelenggara', $involvement?->organizerName());
                            })
                            ->required(),
                        TextInput::make('penyelenggara')
                            ->readOnly(function (Get $get): bool {
                                $involvementId = $get('involvement_id');

                                return filled($involvementId)
                                    && (bool) Involvement::query()->find($involvementId)?->is_lprl_organizer;
                            })
                            ->required()
                            ->label('Penyelenggara')
                            ->maxLength(255),
                        TextInput::make('total_peserta')
                            ->required()
                            ->label('Total peserta')
                            ->numeric()
                            ->suffix('Orang')
                            ->maxLength(10),
                        Radio::make('total_wanita')
                            ->label('Persentase wanita')
                            ->options([
                                0 => '0 %',
                                10 => '10 %',
                                20 => '20 %',
                                30 => '30 %',
                                40 => '40 %',
                                50 => '50 %',
                                60 => '60 %',
                                70 => '70 %',
                                80 => '80 %',
                                90 => '90 %',
                                100 => '100 %',
                            ])
                            ->inline()
                            ->inlineLabel(false)
                            ->columnSpanFull()
                            ->required(),
                        RichEditor::make('how')
                            ->required()
                            ->label('How')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike'],
                                ['h2', 'h3'],
                                ['bulletList', 'orderedList', 'blockquote'],
                                ['link'],
                                ['table'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachments(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Dokumentasi')
                    ->description('Unggah foto kegiatan, surat tugas, dan dokumentasi tambahan yang relevan.')
                    ->icon('heroicon-o-camera')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Fieldset::make('Berkas dokumentasi')
                            ->relationship('documentation')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 3,
                            ])
                            ->columnSpanFull()
                            ->schema([
                                FileUpload::make('dokumentasi1')
                                    ->required()
                                    ->label('Dokumentasi Kegiatan 1')
                                    ->uploadingMessage('Mengunggah dokumentasi...')
                                    ->disk('public')
                                    ->directory('dokumentasi')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->openable()
                                    ->maxSize(3300),
                                FileUpload::make('dokumentasi2')
                                    ->label('Dokumentasi Kegiatan 2')
                                    ->uploadingMessage('Mengunggah dokumentasi...')
                                    ->disk('public')
                                    ->directory('dokumentasi')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(3300)
                                    ->openable(),
                                FileUpload::make('dokumentasi3')
                                    ->label('Dokumentasi Kegiatan 3')
                                    ->uploadingMessage('Mengunggah dokumentasi...')
                                    ->disk('public')
                                    ->directory('dokumentasi')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(3300)
                                    ->openable(),
                                FileUpload::make('st')
                                    ->label('Surat Tugas')
                                    ->nullable()
                                    ->uploadingMessage('Mengunggah dokumentasi...')
                                    ->disk('public')
                                    ->directory('st')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5000)
                                    ->openable(),
                                FileUpload::make('lainnya')
                                    ->label('Dokumentasi Lainnya')
                                    ->nullable()
                                    ->uploadingMessage('Mengunggah dokumentasi...')
                                    ->disk('public')
                                    ->directory('lainnya')
                                    ->visibility('public')
                                    ->openable()
                                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(10420),
                            ]),
                    ]),
                Section::make('Pengesahan')
                    ->description('Tanda tangan disimpan bersama laporan untuk proses pengesahan.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        SignaturePad::make('kode')
                            ->label('Tanda Tangan Penyusun')
                            ->helperText('Pastikan tanda tangan terlihat jelas sebelum menyimpan laporan.')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('Penyusun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('what')
                    ->searchable()
                    ->sortable()
                    ->limit(80)
                    ->label('What'),
                TextColumn::make('when')
                    ->date()
                    ->sortable(),
                TextColumn::make('no_st')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('penyelenggara')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_peserta')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_wanita')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([10, 25, 50, 75])
            ->defaultSort('when', 'desc')
            ->filters([
                Filter::make('when')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('created_until')
                            ->label('Tanggal Selesai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                function ($query) use ($data) {
                                    return $query->whereDate('when', '>=', $data['created_from']);
                                }
                            )
                            ->when(
                                $data['created_until'],
                                function ($query) use ($data) {
                                    return $query->whereDate('when', '<=', $data['created_until']);
                                }
                            );
                    })->indicator('when'),

                SelectFilter::make('indicators')
                    ->label('IKU')
                    ->multiple()
                    ->searchable()
                    ->options(function () {
                        return Indicator::select('id', 'nama_iku', 'tahun_iku')
                            ->orderByDesc('tahun_iku')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->id => "{$item->nama_iku} ({$item->tahun_iku})"];
                            })
                            ->toArray();
                    })
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }

                        $query->whereHas('indicators', function ($q) use ($data) {
                            $q->whereIn('indicators.id', (array) $data['value']);
                        });
                    })
                    ->indicator('IKU'),

                SelectFilter::make('teams')
                    ->relationship('teams', 'nama_tim')
                    ->label('Tim Kerja')
                    ->options(Team::pluck('nama_tim', 'id'))
                    ->preload()
                    ->multiple()
                    ->indicator('Tim Kerja')
                    ->searchable(),
                SelectFilter::make('workUnits')
                    ->relationship('workUnits', 'name')
                    ->label('Unit Kerja')
                    ->preload()
                    ->multiple()
                    ->indicator('Unit Kerja')
                    ->searchable(),
                SelectFilter::make('involvement')
                    ->relationship('involvement', 'name')
                    ->label('Keterlibatan')
                    ->preload()
                    ->multiple()
                    ->indicator('Keterlibatan')
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Penyusun')
                    ->options(User::pluck('name', 'id'))
                    ->preload()
                    ->indicator('Penyusun')
                    ->multiple()
                    ->searchable(),

                TrashedFilter::make(),

            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(3)

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    // Tables\Actions\EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('export_excel')
                        ->label('Export Excel')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->modalHeading('Export Excel')
                        ->modalDescription('File Excel akan dibuat langsung di layar ini. Mohon tunggu sampai proses selesai dan unduhan dimulai.')
                        ->modalSubmitActionLabel('Export sekarang')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => Excel::download(
                            new ReportsExport($records),
                            'laporan-5w1h-'.now()->format('Ymd-His').'.xlsx',
                        )),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $report = $schema->getRecord();
        $dokumentasi = $report?->documentation;
        $publicFileUrl = static function (?string $path): ?string {
            $path = ltrim(trim($path ?? ''), '/');

            if (blank($path)) {
                return null;
            }

            try {
                if (! Storage::disk('public')->exists($path)) {
                    return null;
                }

                return Storage::disk('public')->url($path);
            } catch (\Throwable) {
                return null;
            }
        };

        $documentationSchema = [];
        $otherDocumentation = [];

        if ($url = $publicFileUrl($dokumentasi?->dokumentasi1)) {
            $documentationSchema[] = ImageEntry::make('documentation.dokumentasi1')
                ->height(300)
                ->url($url, '_blank')
                ->extraImgAttributes(['class' => 'report-infolist-media'])
                ->label('Dokumentasi Kegiatan 1');
        }

        if ($url = $publicFileUrl($dokumentasi?->dokumentasi2)) {
            $documentationSchema[] = ImageEntry::make('documentation.dokumentasi2')
                ->height(300)
                ->url($url, '_blank')
                ->extraImgAttributes(['class' => 'report-infolist-media'])
                ->label('Dokumentasi Kegiatan 2');
        }

        if ($url = $publicFileUrl($dokumentasi?->dokumentasi3)) {
            $documentationSchema[] = ImageEntry::make('documentation.dokumentasi3')
                ->height(300)
                ->url($url, '_blank')
                ->extraImgAttributes(['class' => 'report-infolist-media'])
                ->label('Dokumentasi Kegiatan 3');
        }

        if ($url = $publicFileUrl($dokumentasi?->st)) {
            $otherDocumentation[] = IconEntry::make('documentation.st')
                ->label('Surat Tugas')
                ->size(IconSize::Large)
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url($url, '_blank');
        }

        if ($url = $publicFileUrl($dokumentasi?->lainnya)) {
            $otherDocumentation[] = IconEntry::make('documentation.lainnya')
                ->label('Dokumentasi Lainnya')
                ->size(IconSize::Large)
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url($url, '_blank');
        }

        $components = [
            Section::make('Informasi Penyusun')
                ->description('Identitas penyusun dan referensi surat tugas laporan.')
                ->icon('heroicon-o-user-group')
                ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--author'])
                ->columns([
                    'default' => 1,
                    'md' => 3,
                ])
                ->schema([
                    TextEntry::make('user.name')
                        ->label('Penyusun')
                        ->weight(FontWeight::SemiBold)
                        ->extraAttributes(['class' => 'report-infolist-value']),
                    TextEntry::make('followers.name')
                        ->listWithLineBreaks()
                        ->bulleted()
                        ->label('Pengikut')
                        ->placeholder('Tidak ada pengikut')
                        ->columnSpan(1)
                        ->extraAttributes(['class' => 'report-infolist-value']),
                    TextEntry::make('no_st')
                        ->label('Nomor surat tugas')
                        ->placeholder('Belum diisi')
                        ->extraAttributes(['class' => 'report-infolist-value']),
                ])
                ->collapsible()
                ->columnSpanFull(),

            Section::make('Informasi Kegiatan')
                ->description('Ringkasan tujuan, waktu, lokasi, indikator, dan tim kerja.')
                ->icon('heroicon-o-clipboard-document-list')
                ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--activity'])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->schema([
                    TextEntry::make('what')
                        ->label('What')
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'report-infolist-value report-infolist-value--long']),
                    TextEntry::make('why')
                        ->label('Why')
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'report-infolist-value report-infolist-value--long']),
                    Group::make()
                        ->schema([
                            TextEntry::make('when')
                                ->date('d-m-Y')
                                ->label('Tanggal mulai')
                                ->extraAttributes(['class' => 'report-infolist-value']),
                            TextEntry::make('tanggal_selesai')
                                ->date('d-m-Y')
                                ->label('Tanggal selesai')
                                ->extraAttributes(['class' => 'report-infolist-value']),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    TextEntry::make('where')
                        ->label('Where')
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'report-infolist-value report-infolist-value--long']),
                    TextEntry::make('indicators.nama_iku')
                        ->listWithLineBreaks()
                        ->bulleted()
                        ->label('IKU')
                        ->placeholder('Belum ditentukan')
                        ->extraAttributes(['class' => 'report-infolist-value']),
                    TextEntry::make('teams.nama_tim')
                        ->listWithLineBreaks()
                        ->bulleted()
                        ->label('Tim kerja')
                        ->placeholder('Belum ditentukan')
                        ->extraAttributes(['class' => 'report-infolist-value']),
                    TextEntry::make('workUnits.name')
                        ->listWithLineBreaks()
                        ->bulleted()
                        ->label('Unit Kerja')
                        ->placeholder('Belum ditentukan')
                        ->extraAttributes(['class' => 'report-infolist-value']),
                ])
                ->collapsible()
                ->columnSpanFull(),

            Section::make('Informasi Peserta')
                ->description('Penyelenggara, pihak yang terlibat, dan komposisi peserta.')
                ->icon('heroicon-o-users')
                ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--participants'])
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->schema([
                    TextEntry::make('involvement.name')
                        ->label('Keterlibatan')
                        ->placeholder('Belum ditentukan')
                        ->extraAttributes(['class' => 'report-infolist-value']),
                    TextEntry::make('penyelenggara')
                        ->label('Penyelenggara')
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'report-infolist-value report-infolist-value--long']),
                    TextEntry::make('who')
                        ->label('Who')
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'report-infolist-value report-infolist-value--long']),
                    Group::make()
                        ->schema([
                            TextEntry::make('total_peserta')
                                ->label('Total peserta')
                                ->suffix(' orang')
                                ->extraAttributes(['class' => 'report-infolist-value']),
                            TextEntry::make('total_wanita')
                                ->label('Persentase wanita')
                                ->suffix('%')
                                ->extraAttributes(['class' => 'report-infolist-value']),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->columnSpanFull(),

            Section::make('Informasi Pelaksanaan')
                ->description('Uraian lengkap pelaksanaan kegiatan dan tanggal penyusunan.')
                ->icon('heroicon-o-document-text')
                ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--execution'])
                ->columns(1)
                ->schema([
                    TextEntry::make('how')
                        ->formatStateUsing(fn (?string $state): View => view(
                            'infolists.components.how',
                            ['state' => $state],
                        ))
                        ->label('How')
                        ->extraAttributes(['class' => 'report-infolist-rich-text'])
                        ->columnSpanFull(),
                    TextEntry::make('created_at')
                        ->date('d-m-Y')
                        ->label('Tanggal penyusunan')
                        ->extraAttributes(['class' => 'report-infolist-value']),
                ])
                ->collapsible()
                ->columnSpanFull(),
        ];

        if ($documentationSchema !== []) {
            $components[] = Section::make('Dokumentasi Kegiatan')
                ->description('Foto kegiatan yang tersedia pada penyimpanan lokal.')
                ->icon('heroicon-o-camera')
                ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--documentation'])
                ->schema($documentationSchema)
                ->columns([
                    'default' => 1,
                    'md' => 2,
                    'xl' => 3,
                ])
                ->collapsible()
                ->columnSpanFull();
        }

        if ($otherDocumentation !== []) {
            $components[] = Section::make('Dokumentasi Tambahan')
                ->description('Buka surat tugas atau berkas dokumentasi tambahan.')
                ->icon('heroicon-o-paper-clip')
                ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--attachments'])
                ->schema($otherDocumentation)
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->collapsible()
                ->columnSpanFull();
        }

        $components[] = Section::make('Tanda Tangan')
            ->description('Tanda tangan penyusun yang tersimpan bersama laporan.')
            ->icon('heroicon-o-pencil-square')
            ->extraAttributes(['class' => 'report-infolist-section report-infolist-section--signature'])
            ->schema([
                ImageEntry::make('kode')
                    ->height(150)
                    ->extraImgAttributes(['class' => 'signature-preview__image report-infolist-signature'])
                    ->label('Tanda tangan penyusun'),
            ])
            ->columns(1)
            ->collapsible()
            ->columnSpanFull();

        return $schema
            ->components($components)
            ->columns(1);
    }

    public static function getRelations(): array
    {
        return [
            EvaluationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'create' => CreateReport::route('/create'),
            'view' => ViewReport::route('/{record}'),
            'edit' => EditReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return 'Laporan 5W1H';
        } else {
            return 'Report';
        }
    }
}
