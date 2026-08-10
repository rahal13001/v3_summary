<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Services\FCMservice;
use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Order;
use App\Models\Executor;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-c-chat-bubble-oval-left-ellipsis';
    protected static ?string $slug = 'disposisi';
    protected static string | \UnitEnum | null $navigationGroup = 'Executive Summary';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Fieldset::make()
                            ->schema([
                                Select::make('user_id')
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

                                Select::make('order_status')
                                    ->label('Status Tugas')
                                    ->options([
                                        'penting'=>'Penting',
                                        'biasa' => 'Biasa'
                                ])
                                ->required(),

                                // Forms\Components\Select::make('users')
                                //     ->label('Pelaksana Tugas')
                                //     ->relationship(
                                //         name : 'users',
                                //         titleAttribute: 'name',
                                //         modifyQueryUsing: fn ($query) => $query->where('users.status', 1),
                                //     )
                                //     ->preload()
                                //     ->searchable()
                                //     ->required()
                                //     ->multiple()
                                //     ->columnSpanFull(),

                                


                                TextInput::make('instruction')
                                    ->label('Perintah Tugas')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                    Section::make()
                    ->schema([
                        Fieldset::make()
                            ->schema([
                                Repeater::make('executor')
                                    ->label('Pelaksana Tugas')
                                    ->columnSpanFull()
                                    ->relationship('executor') // ✅ Use the relationship correctly
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Pegawai')
                                            ->searchable()
                                            ->preload()
                                            ->relationship(
                                                name : 'user',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->where('users.status', 1),
                                            )
                                            ->required(),
                                        TextInput::make('task')
                                            ->label('Tugas Individu')
                                            ->required(),
                                ]),
                            ]),
                    ]),

                
                Section::make()
                    ->schema([
                        Fieldset::make()
                            ->schema([
                                DatePicker::make('order_date')
                                     ->label('Tanggal Mulai Tugas'),

                                DatePicker::make('order_finishdate')
                                     ->label('Tanggal Selesai Tugas'),
            
                                TimePicker::make('order_time')
                                     ->label('Waktu Tugas'),
                                
                                Textarea::make('note')
                                     ->label('Catatan')
                                     ->columnSpanFull(),
                            ])->columns(3),
                    ]),
             
                Section::make()
                    ->schema([
                        FileUpload::make('letter')
                            ->label('Dokumen Lampiran')
                            ->openable()
                            ->directory('perintah_disposisi')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/jpeg', 'image/png', 'image/webp'])
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
                TextColumn::make('order_date')
                    ->label('Tanggal Tugas')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pemberi Perintah')
                    ->searchable()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('instruction')
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
                TextColumn::make('order_status')
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

                TextColumn::make('pegawaidapatDisposisi')
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
                
                TextColumn::make('pegawaiSelesai')
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
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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
                    ->schema([
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
            ->recordActions([
                // Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('test_fcm')
                ->label('Infokan')
                ->visible(function ($record) {
                    // Get the authenticated user
                    $user = Auth::user();

                    // If user has role 'writer', only allow editing their own records
                    if ($user->can('create', Order::class)) {
                        return true;

                    }
                    return $record->user_id === $user->id;
                    // For other roles (like admin), always show edit button
                })
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->action(function (Model $record, array $data): void {
                    try {
                        $executors = Executor::where('order_id', $record->id)->get();
                        $fcmService = app()->make(FCMservice::class);

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
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('An error occurred: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            ])
            ->paginated([10, 25, 50, 75])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('bulk_test_fcm')
                    ->label('Infokan')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->action(function (Collection $records): void {
                        try {
                            $fcmService = app()->make(FCMservice::class);

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
                        } catch (Exception $e) {
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('executor')
            ->withCount([
                'executor as executors_count',
                'executor as completed_executors_count' => fn (Builder $query): Builder => $query->where('status', 1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
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
