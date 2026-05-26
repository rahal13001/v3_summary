<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $slug = 'pengguna';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Admin Area';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nip')
                    ->label('NIP / ID')
                    ->maxLength(18),
                Forms\Components\TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name', fn ($query) => 
                        $query->when(!Auth::user()->hasRole('super_admin'), fn ($q) => $q->where('name', '!=', 'super_admin'))
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status')
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
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('test_fcm')
                    ->label('Test FCM')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn (Model $record): bool => !empty($record->fcm_token))
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->placeholder('Notification Title'),
                            
                        Forms\Components\Textarea::make('body')
                            ->required()
                            ->placeholder('Notification Body'),
                    ])
                    ->action(function (Model $record, array $data): void {
                        try {
                            $fcmService = app()->make(\App\Services\FCMservice::class);
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
                        } catch (\Exception $e) {
                            // Handle the exception
                            Notification::make()
                                ->title('Error')
                                ->body('An error occurred: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
