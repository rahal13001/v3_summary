<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Team;
use App\Models\User;
use Filament\Tables;
use App\Models\Report;
use Filament\Forms\Form;
use App\Models\Indicator;
use Filament\Tables\Table;
use App\Models\Documentation;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Exports\ReportExporter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\ReportResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;
use App\Filament\Resources\ReportResource\RelationManagers;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Filament\Infolists\Components\Group as ListGroup;
use Illuminate\Contracts\View\View;
use App\Infolists\Components\How;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-document';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'laporan-5w1h';

    protected static ?string $navigationGroup = 'Executive Summary';

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->required()
                    ->relationship('user', 'name')
                    ->options(User::all()->pluck('name', 'id'))
                    ->preload()
                    ->label('Penyusun')
                    ->searchable()
                    ->searchPrompt('Cari nama pegawai LPSPL Sorong')
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('followers.name')
                    ->nullable()
                    ->relationship('followers', 'name')
                    ->options(User::all()->pluck('name', 'id'))
                    ->preload()
                    ->label('Pengikut')
                    ->searchable()
                    ->searchPrompt('Cari nama pegawai LPSPL Sorong')
                    ->multiple()
                    ->searchPrompt('Cari nama pegawai LPSPL Sorong')
                    ->columnSpanFull(),
                                
                Forms\Components\TextInput::make('no_st')
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpanFull(),
                
                
                Forms\Components\Textarea::make('what')
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('indicator_id')
                    ->relationship('indicators', 'indicator_id')
                    ->label('IKU')
                    ->options(Indicator::where('status_iku', 'aktif')->pluck('nama_iku', 'id'))
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->searchPrompt('Cari indikator')
                    ->columnSpanFull(),

                Forms\Components\Select::make('team_id')
                    ->relationship('teams', 'team_id')
                    ->label('Tim Kerja')
                    ->options(Team::where('status_tim', 'aktif')->pluck('nama_tim', 'id'))
                    ->preload()
                    ->searchable()
                    ->searchPrompt('Cari Tim Kerja')
                    ->multiple()
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('why')
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\DatePicker::make('when')
                    ->required(),
                
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->required(),
                
                Forms\Components\Textarea::make('where')
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('who')
                    ->required()
                    ->columnSpanFull(),
                                
                TinyEditor::make('how')
                    ->required()
                    ->columnSpanFull(),
                    
                Forms\Components\TextInput::make('penyelenggara')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_peserta')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Radio::make('total_wanita')
                    ->label('Total Wanita')
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
               
                    Fieldset::make('Dokumentasi')
                    ->relationship('documentation')
                    ->schema([
                        FileUpload::make('dokumentasi1')
                            ->required()
                            ->label('Dokumentasi Kegiatan 1')
                            ->uploadingMessage('Mengunggah dokumentasi...')
                            ->disk('public')
                            ->directory('dokumentasi')
                            ->visibility('public')
                            ->image()
                            ->openable()
                            ->maxSize(3300)
                            ->columnSpanFull(),
                        
                        FileUpload::make('dokumentasi2')
                            ->label('Dokumentasi Kegiatan 2')
                            ->uploadingMessage('Mengunggah dokumentasi...')
                            ->disk('public')
                            ->directory('dokumentasi')
                            ->visibility('public')
                            ->image()
                            ->maxSize(3300)
                            ->openable(),
                       
                        FileUpload::make('dokumentasi3')
                            ->label('Dokumentasi Kegiatan 3')
                            ->uploadingMessage('Mengunggah dokumentasi...')
                            ->disk('public')
                            ->directory('dokumentasi')
                            ->visibility('public')
                            ->image()
                            ->maxSize(3300)
                            ->openable(),
                        
                        FileUpload::make('st')
                            ->label('Surat Tugas')
                            ->nullable()
                            ->uploadingMessage('Mengunggah dokumentasi...')
                            ->disk('public')
                            ->directory('st')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'image/*'])
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
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'image/*'])
                            ->maxSize(10420)
                            ->openable(),
                          
                    ]),
                        SignaturePad::make('kode')
                            ->label(__('Tanda Tangan Disini'))
                            ->dotSize(1.5)
                            ->lineMinWidth(0.5)
                            ->lineMaxWidth(2.5)
                            ->throttle(16)
                            ->minDistance(5)
                            ->velocityFilterWeight(0.7)
                            ->exportPenColor('#0000FF') 
                            ->columns(4),
                ]);
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
            ->defaultSort('when', 'desc')
            ->filters([
                Filter::make('when')
                ->form([
                    DatePicker::make('created_from')
                        ->label('Tanggal Mulai'),
                    DatePicker::make('created_until')
                        ->label('Tanggal Selesai'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            function($query) use ($data) {
                                return $query->whereDate('when', '>=', $data['created_from']);
                            }
                        )
                        ->when(
                            $data['created_until'],
                            function($query) use ($data) {
                                return $query->whereDate('when', '<=', $data['created_until']);
                            }
                        );
                })->indicator('when'),
            
                SelectFilter::make('indicators')
                    ->relationship('indicators', 'nama_iku')
                    ->label('IKU')
                    ->options(Indicator::pluck('nama_iku'))
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->indicator('IKU'),
                SelectFilter::make('teams')
                    ->relationship('teams', 'nama_tim')
                    ->label('Tim Kerja')
                    ->options(Team::pluck('nama_tim'))
                    ->preload()
                    ->multiple()
                    ->indicator('Tim Kerja')
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Penyusun')
                    ->options(User::pluck('name', 'id'))
                    ->preload()
                    ->indicator('Penyusun')
                    ->multiple()
                    ->searchable(),
                
                Tables\Filters\TrashedFilter::make(),

            ],layout: FiltersLayout::Modal )
            ->filtersFormColumns(3)

            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    ExportBulkAction::make()->exporter(ReportExporter::class),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        
            $dokumentasi = Documentation::where('report_id', $infolist->record->id)
                ->first(['dokumentasi1', 'dokumentasi2', 'dokumentasi3', 'st', 'lainnya']);

            $documentationSchema = [];
            $otherDocumentation = [];

            if ($dokumentasi->dokumentasi1){
                $documentationSchema[] = ImageEntry::make('documentation.dokumentasi1')
                    ->height(300)
                    ->url(asset('storage/' . $dokumentasi->dokumentasi1), '_blank')
                    ->label('Dokumentasi Kegiatan 2');
            }
            if ($dokumentasi->dokumentasi2){
                $documentationSchema[] = ImageEntry::make('documentation.dokumentasi2')
                    ->height(300)
                    ->url(asset('storage/' . $dokumentasi->dokumentasi2), '_blank')
                    ->label('Dokumentasi Kegiatan 2');
            }

            if ($dokumentasi->dokumentasi3){
                $documentationSchema[] = ImageEntry::make('documentation.dokumentasi3')
                    ->height(300)
                    ->url(asset('storage/' . $dokumentasi->dokumentasi3), '_blank')
                    ->label('Dokumentasi Kegiatan 3');
            }

            if ($dokumentasi->st){
                $otherDocumentation[] = IconEntry::make('documentation.st')
                    ->label('Surat Tugas')
                    ->size(IconEntry\IconEntrySize::Large)
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(asset('storage/' . $dokumentasi->st), '_blank');
            }

            if ($dokumentasi->lainnya){
                $otherDocumentation[] = 
                    IconEntry::make('documentation.lainnya')
                    ->label('Dokumentasi Lainnya')
                    ->size(IconEntry\IconEntrySize::Large)
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(asset('storage/' . $dokumentasi->lainnya), '_blank');
            }


            return $infolist
                ->schema([
                    Section::make('Informasi Penyusun')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Penyusun')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('followers.name')
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->weight(FontWeight::Bold)
                            ->label('Pengikut'),
                        TextEntry::make('no_st')
                            ->weight(FontWeight::Bold)
                            ->label('No ST'),
                    ])->columns(2)
                    ->collapsible(),

                    Section::make('Informasi Kegiatan')
                        ->schema([
                            TextEntry::make('what')
                                ->weight(FontWeight::Bold)
                                ->label('What'),
                            ListGroup::make()
                                ->schema([
                                    TextEntry::make('when')
                                        ->date('d-m-Y')
                                        ->weight(FontWeight::Bold)
                                        ->label('Tanggal Mulai'),
                                    TextEntry::make('tanggal_selesai')
                                        ->date('d-m-Y')
                                        ->weight(FontWeight::Bold)
                                        ->label('Tanggal Selesai'),
                                ])->columns(2),
                            TextEntry::make('where')
                                ->weight(FontWeight::Bold)
                                ->label('Where'),
                            TextEntry::make('indicators.nama_iku')
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->weight(FontWeight::Bold)
                                ->label('IKU'),
                            TextEntry::make('teams.nama_tim')
                                ->listWithLineBreaks()
                                ->bulleted()
                                ->weight(FontWeight::Bold)
                                ->label('Tim Kerja'),
                            
                        
                        ]) ->collapsible(),

                    Section::make('Informasi Peserta')
                        ->schema([
                            TextEntry::make('penyelenggara')
                                ->weight(FontWeight::Bold)
                                ->label('Penyelenggara'),
                            TextEntry::make('who')
                                ->weight(FontWeight::Bold)
                                ->label('Who'),
                            ListGroup::make()
                                ->schema([
                                    TextEntry::make('total_peserta')
                                        ->weight(FontWeight::Bold)
                                        ->label('Total Peserta')
                                        ->suffix(' orang'),
                                    TextEntry::make('total_wanita')
                                        ->weight(FontWeight::Bold)
                                        ->suffix('%')
                                        ->label('Persentase Wanita'),
                                ])->columns(2),
                        ]) ->collapsible(),

                    Section::make('Informasi Pelaksanaan')
                        ->schema([
                            TextEntry::make('how')
                            // ->html()
                            ->formatStateUsing(fn (string $state): View => view(
                                'infolists.components.how',
                                ['state' => $state],
                            ))
                            ->label('How'),
                            TextEntry::make('created_at')
                                ->date('d-m-Y')
                                ->weight(FontWeight::Bold)
                                ->label('Tanggal Penyusunan'),
                        ]) ->collapsible(),
                    
                    Section::make('Dokumentasi Kegiatan')
                        ->schema($documentationSchema)
                        ->columns(2)
                        ->collapsible(),
                    Section::make('Dokumentasi Tambahan')
                        ->schema($otherDocumentation)
                        ->columns(2)
                        ->collapsible(),
                    Section::make('Tanda Tangan')
                        ->schema([
                            ImageEntry::make('kode')
                                ->height(150)
                                ->label('Tanda Tangan Penyusun')
                        ])
                        ->columns(1)
                        ->collapsible(),
                        
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
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
            return "Laporan 5W1H";
        }
        else
        {
            return "Report";
        }
    }
}
