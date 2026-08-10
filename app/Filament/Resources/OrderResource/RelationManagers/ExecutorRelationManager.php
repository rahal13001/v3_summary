<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Models\Executor;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ExecutorRelationManager extends RelationManager
{
    protected static string $relationship = 'executor';
    protected static ?string $title = 'Aktivitas Pelaksana Tugas';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Pelaksana Tugas')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Selesai',
                        false => 'Belum Selesai',
                    ])
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Select::make('report_id')
                    ->label('Laporan 5W1H')
                    ->relationship('report', 'what')
                    ->searchable()
                    ->columnSpanFull(),

                FileUpload::make('proof')
                    ->label('Bukti Dukung')
                    ->directory('tindakLanjutDispo')
                    ->visibility('public')
                    ->openable()
                    ->maxSize(3072)
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/jpeg', 'image/png', 'image/webp'])
                    ->uploadingMessage('Dokumen sedang diupload')
                    ->columnSpanFull()
                    ->required(),
                    
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(),
                BooleanColumn::make('status')
                    ->label('Status')
                    ->trueIcon('heroicon-o-check-circle')   // Green check icon for true
                    ->falseIcon('heroicon-o-x-circle')      // Red cross icon for false
                    ->trueColor('success')                  // Green color for true
                    ->falseColor('danger'),          // Red color for false
                
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('refresh')
                    ->outlined()
                    ->dispatchSelf('refreshComments'),
               
            ])
            ->recordActions([
                Action::make('summary')
                    ->label('5W1H')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(function ($record) {
                        if ($record->report_id) {
                            return true;
                        }
                    })
                    ->url(fn (Executor $record): string => url('/laporan-5w1h/' . $record->report->slug))
                    ->openUrlInNewTab(),

                Action::make('proof')
                    ->label('Dokumetasi')
                    ->icon('heroicon-o-photo')
                    ->color('blue')
                    ->visible(function ($record) {
                        if ($record->proof) {
                            return true;
                        }
                    })
                    ->url(fn (Executor $record): string => url('https://summary.timurbersinar.com/' . $record->proof))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->modalHeading('Sesuaikan Data')
                    ->modalWidth('5x1')
                    ->closeModalByClickingAway(false)
                    ->visible(function ($record) {
                        // Get the authenticated user
                        $user = Auth::user();
                        
                        // If user has role 'writer', only allow editing their own records
                        if ($user->can('create', Order::class)) {
                            return true;

                        } elseif ($record->user && $record->user->id === $user->id) {
                            return true;   
                        }
                        
                    }),
               
                DeleteAction::make()
                    ->visible(function ($record) {
                        // Get the authenticated user
                        $user = Auth::user();
                        
                        // If user has role 'writer', only allow editing their own records
                        if ($user->can('create', Order::class)) {
                            return true;
                        }
                        
                        // For other roles (like admin), always show edit button
                        return false;
                    }),
                
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->visible(function () {
                        // Get the authenticated user
                        $user = Auth::user();
                        
                        // If user has role 'writer', hide create button
                        if ($user->hasRole('writer')) {
                            return false;
                        }
                        
                        // For other roles (like admin), show create button
                        return true;
                    }),
                ]),
            ]);
    }

    #[On('refreshComments')]
    public function refresh(): void
    {}

    public function isReadOnly(): bool
    {
        return false;
    }
}
