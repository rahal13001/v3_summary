<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\WorkUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MonevDatasetService
{
    /**
     * @return array{INTERNAL: array<int, array<string, mixed>>, EKSTERNAL: array<int, array<string, mixed>>}
     */
    public function build(WorkUnit $workUnit, mixed $period): array
    {
        $month = Carbon::parse($period)->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $periodDate = $month->toDateString();

        $reports = Report::query()
            ->whereHas(
                'workUnits',
                fn (Builder $query): Builder => $query->where('work_units.id', $workUnit->getKey()),
            )
            ->where(function (Builder $query) use ($month, $monthEnd, $workUnit, $periodDate): void {
                $query->where(function (Builder $query) use ($month, $monthEnd): void {
                    $query->whereDate('when', '<=', $monthEnd->toDateString())
                        ->whereDate('tanggal_selesai', '>=', $month->toDateString());
                })->orWhereHas('evaluations', function (Builder $query) use ($workUnit, $periodDate): void {
                    $query->where('work_unit_id', $workUnit->getKey())
                        ->whereDate('period', $periodDate);
                });
            })
            ->with([
                'involvement:id,is_lprl_organizer',
                'evaluations' => fn ($query) => $query
                    ->where('work_unit_id', $workUnit->getKey())
                    ->whereDate('period', $periodDate),
            ])
            ->orderBy('when')
            ->orderBy('what')
            ->orderBy('id')
            ->get();

        $groups = ['INTERNAL' => [], 'EKSTERNAL' => []];

        foreach ($reports as $report) {
            $group = $report->involvement?->is_lprl_organizer ? 'INTERNAL' : 'EKSTERNAL';
            /** @var ReportEvaluation|null $evaluation */
            $evaluation = $report->evaluations->first();

            $groups[$group][] = $this->row(
                $report,
                $evaluation,
                count($groups[$group]) + 1,
            );
        }

        return $groups;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Report $report, ?ReportEvaluation $evaluation, int $number): array
    {
        return [
            'number' => $number,
            'activity_name' => (string) $report->what,
            'implementation_plan' => (string) ($evaluation?->rencana_pelaksanaan ?? ''),
            'implementation_realization' => $this->dateRange($report->when, $report->tanggal_selesai),
            'activity_result' => $this->plainText((string) $report->how),
            'obstacles' => (string) ($evaluation?->kendala ?? ''),
            'recommendations' => (string) ($evaluation?->saran_rekomendasi ?? ''),
            'follow_up' => (string) ($evaluation?->tindak_lanjut ?? ''),
            'evidence_links' => $evaluation?->evidence_links ?? [],
            'notes' => (string) ($evaluation?->keterangan ?? ''),
        ];
    }

    private function dateRange(mixed $start, mixed $end): string
    {
        $startDate = Carbon::parse($start)->format('d-m-Y');
        $endDate = Carbon::parse($end)->format('d-m-Y');

        return $startDate === $endDate ? $startDate : "{$startDate} s.d. {$endDate}";
    }

    private function plainText(string $html): string
    {
        $withLineBreaks = preg_replace('/<\s*(br|\/p|\/li)\s*\/?>/i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($withLineBreaks), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/[ \t]+/', ' ', $text));
    }
}
