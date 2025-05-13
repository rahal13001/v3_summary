<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Form;
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


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pelaksana Tugas')
                    ->relationship('user', 'name')
                    ->required()
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Selesai',
                        false => 'Belum Selesai',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('report_id')
                    ->label('Laporan 5W1H')
                    ->relationship('report', 'what')
                    ->searchable()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('proof')
                    ->label('Bukti Dukung')
                    ->directory('tindakLanjutDispo')
                    ->visibility('public')
                    ->openable()
                    ->maxSize(3072)
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'image/*'])
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama'),
                Tables\Columns\BooleanColumn::make('status')
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
                Tables\Actions\Action::make('refresh') 
                    ->outlined()
                    ->dispatchSelf('refreshComments'),
                // Tables\Actions\CreateAction::make()
                //     ->modalHeading('Tambah Data')
                //     ->label('Tambah Data')
                //     ->modalWidth('5x1')
                //     ->closeModalByClickingAway(false)
                //     ->visible(function () {
                //         // Get the authenticated user
                //         $user = auth()->user();
                        
                //         // If user has role 'writer', hide create button
                //         if ($user->hasRole('writer')) {
                //             return false;
                //         }
                        
                //         // For other roles (like admin), show create button
                //         return true;
                //     }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Sesuaikan Data')
                    ->modalWidth('5x1')
                    ->closeModalByClickingAway(false)
                    ->visible(function ($record) {
                        // Get the authenticated user
                        $user = Auth::user();
                        
                        // If user has role 'writer', only allow editing their own records
                        if ($user->can('viewAny', Order::class)) {
                            return true;

                        }
                        return $record->user_id === $user->id;
                        // For other roles (like admin), always show edit button
                    }),
                Tables\Actions\DeleteAction::make()
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
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
