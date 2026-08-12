<?php

namespace App\Filament\Resources\ReportResource\RelationManagers;

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\WorkUnit;
use App\Policies\ReportEvaluationPolicy;
use App\Services\OrganizationContext;
use App\Services\ReportEvaluationService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    protected static ?string $title = 'Evaluasi Monev';

    public function form(Schema $schema): Schema
    {
        return $schema->components($this->evaluationFields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Report $report */
                $report = $this->getOwnerRecord();
                $user = auth()->user();
                $policy = app(ReportEvaluationPolicy::class);

                if ($user === null) {
                    return $query->whereRaw('1 = 0');
                }

                if ($policy->hasReportWideAccess($user, $report)) {
                    return $query;
                }

                return $query->whereHas('workUnit.coordinatorAssignments', fn (Builder $query) => $query
                    ->where('user_id', $user->getKey())
                    ->whereDate('starts_at', '<=', today())
                    ->where(fn (Builder $query) => $query
                        ->whereNull('ends_at')
                        ->orWhereDate('ends_at', '>=', today())));
            })
            ->columns([
                TextColumn::make('workUnit.name')->label('Unit Kerja')->sortable(),
                TextColumn::make('period')->label('Periode')->date('F Y')->sortable(),
                TextColumn::make('status')
                    ->state('Sudah dievaluasi')
                    ->badge()
                    ->color('success'),
                TextColumn::make('editor.name')->label('Editor terakhir'),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d-m-Y H:i')->sortable(),
            ])
            ->defaultSort('period', 'desc')
            ->headerActions([
                Action::make('createEvaluation')
                    ->label('Tambah Evaluasi')
                    ->icon('heroicon-o-plus')
                    ->schema($this->evaluationFields(includeIdentity: true))
                    ->action(function (array $data): void {
                        /** @var Report $report */
                        $report = $this->getOwnerRecord();
                        $workUnit = WorkUnit::query()->findOrFail($data['work_unit_id']);
                        $user = auth()->user();

                        abort_unless(
                            $user && app(ReportEvaluationPolicy::class)->createFor($user, $report, $workUnit),
                            403,
                        );

                        app(ReportEvaluationService::class)->create(
                            $report,
                            $workUnit,
                            $data['period'],
                            $data,
                            $user,
                        );
                    }),
            ])
            ->recordActions([
                Action::make('editEvaluation')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (ReportEvaluation $record): array => $record->only(ReportEvaluation::CONTENT_FIELDS))
                    ->schema($this->evaluationFields())
                    ->visible(fn (ReportEvaluation $record): bool => $this->canUpdate($record))
                    ->action(function (ReportEvaluation $record, array $data): void {
                        $user = auth()->user();

                        abort_unless(
                            $user && app(ReportEvaluationPolicy::class)->update($user, $record),
                            403,
                        );

                        app(ReportEvaluationService::class)->update($record, $data, $user);
                    }),
                Action::make('history')
                    ->label('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->visible(fn (ReportEvaluation $record): bool => $this->canViewHistory($record))
                    ->modalHeading('Riwayat Perubahan Evaluasi')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (ReportEvaluation $record) => view(
                        'filament.resources.report.evaluation-history',
                        ['revisions' => $record->revisions()->with('editor')->latest('changed_at')->get()],
                    )),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        return app(OrganizationContext::class)->monevEnabled()
            && $user !== null
            && $ownerRecord instanceof Report
            && app(ReportEvaluationPolicy::class)->canManageReport($user, $ownerRecord);
    }

    private function evaluationFields(bool $includeIdentity = false): array
    {
        $fields = [];

        if ($includeIdentity) {
            $fields[] = Select::make('work_unit_id')
                ->label('Unit Kerja')
                ->options(fn (): array => $this->availableWorkUnitOptions())
                ->searchable()
                ->required();
            $fields[] = DatePicker::make('period')
                ->label('Periode')
                ->displayFormat('F Y')
                ->native(false)
                ->required();
        }

        return [
            ...$fields,
            Textarea::make('rencana_pelaksanaan')->label('Rencana Pelaksanaan')->rows(4)->maxLength(10000),
            Textarea::make('kendala')->label('Kendala')->rows(4)->maxLength(10000),
            Textarea::make('saran_rekomendasi')->label('Saran/Rekomendasi')->rows(4)->maxLength(10000),
            Textarea::make('tindak_lanjut')->label('Tindak Lanjut')->rows(4)->maxLength(10000),
            Repeater::make('evidence_links')
                ->label('Tautan Bukti Dukung')
                ->simple(
                    TextInput::make('url')
                        ->label('URL')
                        ->url()
                        ->rules(['url:http,https'])
                        ->maxLength(2048),
                )
                ->maxItems(20)
                ->addActionLabel('Tambah tautan'),
            Textarea::make('keterangan')->label('Keterangan')->rows(4)->maxLength(10000),
        ];
    }

    private function availableWorkUnitOptions(): array
    {
        /** @var Report $report */
        $report = $this->getOwnerRecord();
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return $report->workUnits()
            ->with('currentCoordinatorAssignment')
            ->orderBy('name')
            ->get()
            ->filter(fn (WorkUnit $unit): bool => app(ReportEvaluationPolicy::class)->createFor($user, $report, $unit))
            ->pluck('name', 'id')
            ->all();
    }

    private function canUpdate(ReportEvaluation $evaluation): bool
    {
        $user = auth()->user();

        return $user !== null && app(ReportEvaluationPolicy::class)->update($user, $evaluation);
    }

    private function canViewHistory(ReportEvaluation $evaluation): bool
    {
        $user = auth()->user();

        return $user !== null && app(ReportEvaluationPolicy::class)->viewHistory($user, $evaluation);
    }
}
