<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\User;
use App\Services\FCMservice;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $slug = 'pengguna';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'Admin Area';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Nama')
                    ->maxLength(255),
                TextInput::make('nip')
                    ->label('NIP / ID')
                    ->maxLength(18),
                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->maxLength(255),
                FileUpload::make('coordinator_signature_path')
                    ->label('Tanda tangan koordinator')
                    ->helperText('PNG, JPEG, atau WebP maksimal 2 MB. Digunakan pada export Monev.')
                    ->disk('local')
                    ->directory('coordinator-signatures')
                    ->visibility('private')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),
                Select::make('roles')
                    ->relationship('roles', 'name', fn ($query) => $query->when(! Auth::user()->hasRole('super_admin'), fn ($q) => $q->where('name', '!=', 'super_admin'))
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nip')
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->searchable(),
                ToggleColumn::make('status')
                    ->visible(function ($record) {
                        // Get the authenticated user
                        $user = Auth::user();

                        // If user has role 'writer', only allow editing their own records
                        if ($user->can('create', User::class)) {
                            return true;

                        }
                    })
                    ->label('Status'),
            ])
            ->defaultSort('name')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('test_fcm')
                    ->label('Test FCM')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn (Model $record): bool => ! empty($record->fcm_token))
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->placeholder('Notification Title'),

                        Textarea::make('body')
                            ->required()
                            ->placeholder('Notification Body'),
                    ])
                    ->action(function (Model $record, array $data): void {
                        try {
                            $fcmService = app()->make(FCMservice::class);
                            $result = $fcmService->sendNotification(
                                $record->fcm_token,
                                $data['title'],
                                $data['body'],
                                [
                                    'test_key' => 'test_value',
                                    'timestamp' => now()->timestamp,
                                ]
                            );

                            if ($result['success']) {
                                // Handle success
                                Notification::make()
                                    ->title('Notification sent successfully')
                                    ->body($result['message'] ?? 'Notification sent successfully')
                                    ->success()
                                    ->send();
                            } else {
                                // Handle failure
                                Notification::make()
                                    ->title('Notification failed')
                                    ->body($result['message'] ?? 'Failed to send notification')
                                    ->danger()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            // Handle the exception
                            Notification::make()
                                ->title('Error')
                                ->body('An error occurred: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
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
        return app()->getLocale() === 'id' ? 'Pengguna' : 'User';
    }

    public static function getPluralLabel(): ?string
    {
        return app()->getLocale() === 'id' ? 'Pengguna' : 'Users';
    }
}
