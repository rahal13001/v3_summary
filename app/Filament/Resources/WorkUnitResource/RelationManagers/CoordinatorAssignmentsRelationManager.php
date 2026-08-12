<?php

namespace App\Filament\Resources\WorkUnitResource\RelationManagers;

use App\Models\User;
use App\Models\WorkUnit;
use App\Models\WorkUnitCoordinator;
use App\Services\CoordinatorAssignmentService;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CoordinatorAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'coordinatorAssignments';

    protected static ?string $title = 'Riwayat Koordinator';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Koordinator')
                ->options(User::query()->where('status', true)->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),
            DatePicker::make('starts_at')
                ->label('Mulai')
                ->required(),
            DatePicker::make('ends_at')
                ->label('Selesai')
                ->afterOrEqual('starts_at'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Koordinator')->searchable(),
                TextColumn::make('user.nip')->label('NIP'),
                TextColumn::make('user.jabatan')->label('Jabatan'),
                TextColumn::make('starts_at')->label('Mulai')->date('d-m-Y'),
                TextColumn::make('ends_at')->label('Selesai')->date('d-m-Y')->placeholder('Aktif'),
            ])
            ->defaultSort('starts_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Tetapkan Koordinator')
                    ->using(function (array $data): Model {
                        /** @var WorkUnit $workUnit */
                        $workUnit = $this->getOwnerRecord();

                        return app(CoordinatorAssignmentService::class)->assign(
                            $workUnit,
                            User::query()->findOrFail($data['user_id']),
                            $data['starts_at'],
                            $data['ends_at'] ?? null,
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(fn (WorkUnitCoordinator $record, array $data): Model => app(CoordinatorAssignmentService::class)
                        ->update($record, $data)),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('update', $ownerRecord) ?? false;
    }
}
