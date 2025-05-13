<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Order;
use App\Models\Executor;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\OrderResource\Pages;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\Widgets\OrdersOverview;
use App\Filament\Resources\OrderResource\Widgets\AllOrdersOverview;
use App\Filament\Resources\OrderResource\RelationManagers\ExecutorRelationManager;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-c-chat-bubble-oval-left-ellipsis';
    protected static ?string $slug = 'disposisi';
    protected static ?string $navigationGroup = 'Executive Summary';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make()
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pemberi Perintah')
                                    ->default(Auth::user()->id)
                                    ->searchable()
                                    ->preload()
                                    ->relationship(
                                        name : 'user',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('status', 1),
                                    )
                                    ->required()
                                    ->columns(2),

                                Forms\Components\Select::make('order_status')
                                    ->label('Status Tugas')
                                    ->options([
                                        'penting'=>'Penting',
                                        'biasa' => 'Biasa'
                                ])
                                ->required(),

                                Forms\Components\Select::make('users')
                                    ->label('Pelaksana Tugas')
                                    ->relationship(
                                        name : 'users',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('users.status', 1),
                                    )
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->multiple()
                                    ->columnSpanFull(),


                                Forms\Components\TextInput::make('instruction')
                                    ->label('Perintah Tugas')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Fieldset::make()
                            ->schema([
                                Forms\Components\DatePicker::make('order_date')
                                     ->label('Tanggal Tugas'),
            
                                Forms\Components\TimePicker::make('order_time')
                                     ->label('Waktu Tugas'),
                                
                                Forms\Components\Textarea::make('note')
                                     ->label('Catatan')
                                     ->columnSpanFull(),
                            ]),
                    ]),
             
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\FileUpload::make('letter')
                            ->label('Dokumen Lampiran')
                            ->openable()
                            ->directory('perintah_disposisi')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'image/*'])
                            ->uploadingMessage('Dokumen sedang diupload')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {   
        return $table
            ->groups([
                Group::make('order_date')
                    ->label('Tanggal Tugas')
                    ->date('d-M-Y')
                    ->collapsible(),
                Group::make('order_date')
                    ->label('Tanggal Tugas')
                    ->date('d-M-Y')
                    ->collapsible(),
                Group::make('user.name')
                    ->label('Pemberi Perintah')
                    ->collapsible(),
            ])
            ->defaultSort('order_date', 'desc')
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tanggal Tugas')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemberi Perintah')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instruction')
                    ->label('Tugas')
                    ->sortable()
                    ->searchable()
                    ->limit(75)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                
                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    })
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'penting' => 'danger',
                        'biasa' => 'success',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pegawaidapatDisposisi')
                    ->label('Pelaksana')
                    ->alignCenter()
                    ->getStateUsing(fn ($record) => $record->pegawaidapatDisposisi())
                    ->summarize(
                        Summarizer::make()
                            ->label('Tot.Pelaksana')
                            ->using(function (QueryBuilder $query) {
                                $data = $query->pluck('id');
                                $pegawai_dispo = Executor::whereIn('order_id', $data)->count();
                                return $pegawai_dispo;
                            }
                            )
                    ),
                
                Tables\Columns\TextColumn::make('pegawaiSelesai')
                    ->label('Selesai')
                    ->alignCenter()
                    ->badge()
                    ->icons(function ($record) {
                        $statusData = $record->userStatus();
                        if (!$statusData) {
                            return [];  // No icon if no status data
                        }
                        return $statusData->status === 1 
                            ? ['heroicon-o-check-circle'] 
                            : ['heroicon-o-x-circle'];
                    })
                    ->color(function ($record) {
                        $statusData = $record->userStatus();
                        if (!$statusData) {
                            return 'gray';  // If no status data found
                        }
                        return $statusData->status === 1 ? 'success' : 'danger';
                    })                        
                    ->getStateUsing(fn ($record) => $record->pegawaiSelesai())
                    ->summarize(
                        Summarizer::make()
                            ->label('Selesai/Belum')
                            ->using(function (QueryBuilder $query) {
                                $data = $query->pluck('id');
                                $data_pegawai = Executor::whereIn('order_id', $data)->get();
                                $selesai = $data_pegawai->where('status', 1)->count();
                                $belum = $data_pegawai->where('status', 0)->count();
                                return $selesai.'/'.$belum;
                            }
                            )
                           
                    ),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Pemberi Perintah')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship('user', 'name'),

                Filter::make('order_date')
                    ->form([
                        DatePicker::make('order_from')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('order_untill')
                            ->label('Tanggal Selesai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_untill'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
  
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test_fcm')
                ->label('Infokan')
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->action(function (Model $record, array $data): void {
                    try {
                        $executors = Executor::where('order_id', $record->id)->get();
                        $fcmService = app()->make(\App\Services\FCMservice::class);

                        foreach ($executors as $executor) {
                            $user = $executor->user;
                            if (!empty($user->fcm_token)) {
                                $result = $fcmService->sendNotification(
                                    $user->fcm_token,
                                    $record->instruction,
                                    "Tanggal: {$record->order_date} - Waktu: {$record->order_time}",
                                    [
                                        'instruction' => $record->instruction,
                                        'date' => $record->order_date,
                                        'time' => $record->order_time,
                                        'timestamp' => now()->timestamp,
                                    ]
                                );

                                if ($result['success']) {
                                    Notification::make()
                                        ->title('Notification sent successfully')
                                        ->body($result['message'] ?? 'Notification sent successfully')
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Notification failed')
                                        ->body($result['message'] ?? 'Failed to send notification')
                                        ->danger()
                                        ->send();
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('An error occurred: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            ])
            ->paginated([10, 25, 50, 75])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_test_fcm')
                    ->label('Infokan')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->action(function (Collection $records): void {
                        try {
                            $fcmService = app()->make(\App\Services\FCMservice::class);

                            foreach ($records as $record) {
                                $executors = Executor::where('order_id', $record->id)->get();

                                foreach ($executors as $executor) {
                                    $user = $executor->user;
                                    if (!empty($user->fcm_token)) {
                                        $result = $fcmService->sendNotification(
                                            $user->fcm_token,
                                            $record->instruction,
                                            "Tanggal: {$record->order_date} - Waktu: {$record->order_time}",
                                            [
                                                'instruction' => $record->instruction,
                                                'date' => $record->order_date,
                                                'time' => $record->order_time,
                                                'timestamp' => now()->timestamp,
                                            ]
                                        );

                                        if ($result['success']) {
                                            Notification::make()
                                                ->title('Notification sent successfully')
                                                ->body($result['message'] ?? 'Notification sent successfully')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Notification failed')
                                                ->body($result['message'] ?? 'Failed to send notification')
                                                ->danger()
                                                ->send();
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('An error occurred: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ]),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            ExecutorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrdersOverview::class,
            AllOrdersOverview::class,
        ];
    }

    public static function getLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'id') {
            return "Disposisi";
        }
        else
        {
            return "Task";
        }
    }
}
