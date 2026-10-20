<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Ai\Access;
use App\Services\Ai\Query;
use App\Services\Ai\Registry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AiController extends Controller
{
    private const NESTED_LIMIT = 25;

    public function __construct(private Access $access, private Registry $registry, private Query $query) {}

    public function capabilities(Request $request): JsonResponse
    {
        return response()->json(['data' => [
            'readonly' => true, 'fields' => $request->attributes->get('ai_fields'),
            'report_scope' => $request->attributes->get('ai_grant')->report_scope,
            'expires_at' => $request->user()->currentAccessToken()->expires_at?->toIso8601String(),
            'timezone' => config('app.timezone'), 'per_page_default' => 25, 'per_page_max' => 100,
            'snapshot' => false, 'attachments' => 'metadata_only',
            'nested_collection_limit' => self::NESTED_LIMIT,
        ]]);
    }

    public function schema(Request $request): JsonResponse
    {
        $data = [];
        foreach ($request->attributes->get('ai_fields') as $dataset => $fields) {
            foreach ($fields as $field) {
                [$type, $description] = $this->registry->definitions()[$dataset][$field];
                $data[$dataset][$field] = ['type' => $type, 'description' => $description, 'nullable' => true,
                    'operators' => $this->registry->operators($type), 'sortable' => $type !== 'array'];
            }
        }

        return response()->json(['data' => $data, 'meta' => [
            'report_identity' => ['id', 'slug', 'source'], 'period_modes' => ['overlap', 'start'],
            'date_format' => 'YYYY-MM-DD', 'relations' => array_values(array_diff(array_keys($data), ['reports'])),
        ]]);
    }

    public function reports(Request $request): JsonResponse
    {
        $query = $this->access->reports($request->user(), $request->attributes->get('ai_grant'));
        $this->query->apply($query, $request, 'reports', $request->attributes->get('ai_fields')['reports']);
        $fields = $this->selection($request, 'reports');
        $includes = $this->includes($request);
        $this->loadRelations($query, $includes, $request);

        return $this->page($request, $query, fn (Report $report) => $this->report($request, $report, $fields, $includes));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        abort_if(array_diff(array_keys($request->query()), ['fields', 'include']), 422, 'Parameter tidak dikenal.');
        $fields = $this->selection($request, 'reports');
        $includes = $this->includes($request);
        $query = $this->access->reports($request->user(), $request->attributes->get('ai_grant'))->whereKey($id);
        $this->loadRelations($query, $includes, $request);
        $report = $query->firstOrFail();
        $request->attributes->set('ai_result_count', 1);

        return response()->json(['data' => $this->report($request, $report, $fields, $includes), 'meta' => $this->meta($request)]);
    }

    public function references(Request $request, string $dataset): JsonResponse
    {
        abort_unless(isset(Registry::REFERENCES[$dataset]), 404);
        $allowed = $request->attributes->get('ai_fields')[$dataset] ?? [];
        abort_unless($allowed, 403, 'Dataset tidak diizinkan.');
        abort_if($request->has('include'), 422, 'Relasi bertingkat tidak tersedia.');
        [$model] = Registry::REFERENCES[$dataset];
        $reachable = $this->access->reports($request->user(), $request->attributes->get('ai_grant'))->select('reports.id');
        $query = $model::query()->whereHas('reports', fn (Builder $reports) => $reports->whereIn('reports.id', $reachable));
        $this->query->apply($query, $request, $dataset, $allowed);
        $fields = $this->selection($request, $dataset);

        return $this->page($request, $query, fn (Model $row) => $this->project($row, $dataset, $fields));
    }

    public function evaluations(Request $request): JsonResponse
    {
        $allowed = $request->attributes->get('ai_fields')['evaluations'] ?? [];
        abort_unless($allowed, 403, 'Evaluasi tidak diizinkan atau Monev nonaktif.');
        abort_if($request->has('include'), 422, 'Relasi bertingkat tidak tersedia.');
        $query = $this->access->evaluations($request->user(), $request->attributes->get('ai_grant'));
        $this->query->apply($query, $request, 'evaluations', $allowed);
        $fields = $this->selection($request, 'evaluations');

        return $this->page($request, $query, fn (Model $row) => $this->project($row, 'evaluations', $fields));
    }

    public function followers(Request $request, string $id): JsonResponse
    {
        $allowed = $request->attributes->get('ai_fields')['people'] ?? [];
        abort_unless($allowed, 403, 'Data orang terkait tidak diizinkan.');
        abort_if($request->has('include'), 422, 'Relasi bertingkat tidak tersedia.');
        $report = $this->access->reports($request->user(), $request->attributes->get('ai_grant'))->whereKey($id)->firstOrFail();
        $query = $report->followers()->getQuery()->select('users.id', 'users.name');
        $this->query->apply($query, $request, 'people', $allowed);
        $fields = $this->selection($request, 'people');

        return $this->page($request, $query, fn (Model $row) => $this->project($row, 'people', $fields));
    }

    private function selection(Request $request, string $dataset): array
    {
        $allowed = $request->attributes->get('ai_fields')[$dataset] ?? [];
        $fields = $request->has('fields') ? $this->query->csv($request->input('fields')) : $allowed;
        foreach ($fields as $field) {
            $this->query->field($field, $dataset, $allowed);
        }

        return $fields;
    }

    private function includes(Request $request): array
    {
        $includes = $this->query->csv($request->input('include', ''));
        foreach ($includes as $dataset) {
            abort_unless($dataset !== 'reports' && isset($this->registry->definitions()[$dataset]), 422, 'Relasi tidak dikenal.');
            abort_unless(isset($request->attributes->get('ai_fields')[$dataset]), 403, 'Relasi tidak diizinkan.');
        }

        return $includes;
    }

    private function loadRelations(Builder $query, array $includes, Request $request): void
    {
        $relations = [];
        foreach ($includes as $dataset) {
            if (isset(Registry::REFERENCES[$dataset])) {
                $relations[] = Registry::REFERENCES[$dataset][1];
            } elseif ($dataset === 'people') {
                $relations[] = 'user:id,name';
                $relations['followers'] = fn ($followers) => $followers->select('users.id', 'users.name')->limit(self::NESTED_LIMIT);
                $query->withCount('followers');
            } elseif ($dataset === 'documentation_metadata') {
                $relations[] = 'documentation';
            } elseif ($dataset === 'evaluations') {
                $allowedIds = $this->access->evaluations($request->user(), $request->attributes->get('ai_grant'))->select('report_evaluations.id');
                $scope = fn ($evaluations) => $evaluations->whereIn('report_evaluations.id', $allowedIds);
                $relations['evaluations'] = fn ($evaluations) => $scope($evaluations)->orderBy('id')->limit(self::NESTED_LIMIT);
                $query->withCount(['evaluations as ai_evaluations_count' => $scope]);
            }
        }
        $query->with($relations);
    }

    private function report(Request $request, Report $report, array $fields, array $includes): array
    {
        $data = ['id' => $report->id, 'slug' => $report->slug, ...$this->project($report, 'reports', $fields)];
        foreach ($includes as $dataset) {
            $selected = $request->attributes->get('ai_fields')[$dataset];
            if (isset(Registry::REFERENCES[$dataset])) {
                $relation = $report->{Registry::REFERENCES[$dataset][1]};
                $data[$dataset] = $relation instanceof Model ? $this->project($relation, $dataset, $selected)
                    : ($relation?->map(fn (Model $row) => $this->project($row, $dataset, $selected))->values()->all() ?? null);
            } elseif ($dataset === 'people') {
                $data[$dataset] = ['author' => $report->user ? $this->project($report->user, $dataset, $selected) : null,
                    'followers' => $report->followers->map(fn (Model $row) => $this->project($row, $dataset, $selected))->all()];
                $data['_relation_meta']['followers'] = ['total' => $report->followers_count, 'returned' => $report->followers->count(),
                    'has_more' => $report->followers_count > $report->followers->count(), 'endpoint' => url('/api/ai/v1/reports/'.$report->id.'/followers')];
            } elseif ($dataset === 'evaluations') {
                $data[$dataset] = $report->evaluations->map(fn (Model $row) => $this->project($row, $dataset, $selected))->all();
                $data['_relation_meta']['evaluations'] = ['total' => $report->ai_evaluations_count, 'returned' => $report->evaluations->count(),
                    'has_more' => $report->ai_evaluations_count > $report->evaluations->count(), 'endpoint' => url('/api/ai/v1/evaluations')];
            } elseif ($dataset === 'documentation_metadata') {
                $kinds = [];
                foreach (['dokumentasi1', 'dokumentasi2', 'dokumentasi3', 'st', 'lainnya'] as $kind) {
                    if (filled($report->documentation?->{$kind})) {
                        $kinds[] = $kind;
                    }
                }
                $data[$dataset] = ['kinds' => $kinds];
            }
        }
        $data['source'] = ['url' => url('/api/ai/v1/reports/'.$report->id), 'updated_at' => $report->updated_at?->toIso8601String()];

        return $data;
    }

    private function project(Model $row, string $dataset, array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $value = $row->getAttribute($field);
            $type = $this->registry->definitions()[$dataset][$field][0];
            if ($value !== null && $type === 'text') {
                $value = trim(html_entity_decode(strip_tags(preg_replace('/<\s*(br|\/p|\/li)\s*\/?>/i', "\n", (string) $value) ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            } elseif ($value !== null && $type === 'date') {
                $value = Carbon::parse($value)->toDateString();
            } elseif ($value !== null && $type === 'datetime') {
                $value = Carbon::parse($value)->toIso8601String();
            }
            $result[$field] = $value;
        }

        return $result;
    }

    private function page(Request $request, Builder $query, callable $serialize): JsonResponse
    {
        $page = $query->paginate((int) $request->input('per_page', 25));
        $request->attributes->set('ai_result_count', $page->count());

        return response()->json(['data' => $page->getCollection()->map($serialize)->values()->all(),
            'meta' => [...$this->meta($request), 'page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'has_more' => $page->hasMorePages()],
            'links' => ['next' => $page->appends($request->query())->nextPageUrl()]]);
    }

    private function meta(Request $request): array
    {
        return ['request_id' => $request->attributes->get('ai_request_id'), 'retrieved_at' => now()->toIso8601String()];
    }
}
