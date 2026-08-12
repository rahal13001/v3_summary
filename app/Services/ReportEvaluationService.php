<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReportEvaluationService
{
    public function create(
        Report $report,
        WorkUnit $workUnit,
        mixed $period,
        array $data,
        User $editor,
    ): ReportEvaluation {
        return DB::transaction(function () use ($report, $workUnit, $period, $data, $editor): ReportEvaluation {
            Report::query()->whereKey($report)->lockForUpdate()->firstOrFail();
            $this->assertReportUnit($report, $workUnit);
            $attributes = $this->validatedContent($data);
            $month = Carbon::parse($period)->startOfMonth()->toDateString();

            $evaluation = ReportEvaluation::query()->create([
                'report_id' => $report->getKey(),
                'work_unit_id' => $workUnit->getKey(),
                'period' => $month,
                ...$attributes,
                'created_by' => $editor->getKey(),
                'updated_by' => $editor->getKey(),
            ]);

            $changes = [
                'report_id' => ['old' => null, 'new' => $report->getKey()],
                'work_unit_id' => ['old' => null, 'new' => $workUnit->getKey()],
                'period' => ['old' => null, 'new' => $month],
            ];

            foreach ($attributes as $field => $value) {
                if ($value !== null) {
                    $changes[$field] = ['old' => null, 'new' => $value];
                }
            }

            $this->recordRevision($evaluation, $editor, $changes);

            return $evaluation;
        });
    }

    public function update(ReportEvaluation $evaluation, array $data, User $editor): ReportEvaluation
    {
        return DB::transaction(function () use ($evaluation, $data, $editor): ReportEvaluation {
            $locked = ReportEvaluation::query()->lockForUpdate()->findOrFail($evaluation->getKey());
            $attributes = $this->validatedContent($data);
            $changes = [];

            foreach ($attributes as $field => $value) {
                $old = $locked->getAttribute($field);

                if ($old !== $value) {
                    $changes[$field] = ['old' => $old, 'new' => $value];
                }
            }

            if ($changes === []) {
                return $locked;
            }

            $locked->fill($attributes);
            $locked->updated_by = $editor->getKey();
            $locked->save();
            $this->recordRevision($locked, $editor, $changes);

            return $locked;
        });
    }

    private function assertReportUnit(Report $report, WorkUnit $workUnit): void
    {
        if ($report->workUnits()->whereKey($workUnit)->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'work_unit_id' => 'Unit Kerja tidak terkait dengan Report ini.',
        ]);
    }

    private function validatedContent(array $data): array
    {
        $content = Arr::only($data, ReportEvaluation::CONTENT_FIELDS);

        if (array_key_exists('evidence_links', $content) && $content['evidence_links'] !== null) {
            $content['evidence_links'] = array_values(array_filter(array_map(
                fn (mixed $url): mixed => is_string($url) ? trim($url) : $url,
                (array) $content['evidence_links'],
            ), fn (mixed $url): bool => filled($url)));
        }

        return Validator::make($content, [
            'rencana_pelaksanaan' => ['nullable', 'string', 'max:10000'],
            'kendala' => ['nullable', 'string', 'max:10000'],
            'saran_rekomendasi' => ['nullable', 'string', 'max:10000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:10000'],
            'evidence_links' => ['nullable', 'array', 'max:20'],
            'evidence_links.*' => ['required', 'string', 'url:http,https', 'max:2048'],
            'keterangan' => ['nullable', 'string', 'max:10000'],
        ])->validate();
    }

    private function recordRevision(ReportEvaluation $evaluation, User $editor, array $changes): void
    {
        $evaluation->revisions()->create([
            'changed_by' => $editor->getKey(),
            'changed_at' => now(),
            'changes' => $changes,
        ]);
    }
}
