<?php

namespace App\Services\Ai;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class Query
{
    public function csv(mixed $input): array
    {
        if (! is_string($input)) {
            throw ValidationException::withMessages(['query' => 'Daftar harus berupa teks dipisahkan koma.']);
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $input)))));
    }

    public function field(string $field, string $dataset, array $allowed): array
    {
        $definition = app(Registry::class)->definitions()[$dataset][$field] ?? null;
        abort_unless($definition, 422, 'Kolom tidak dikenal.');
        abort_unless(in_array($field, $allowed, true), 403, 'Kolom tidak diizinkan untuk token ini.');

        return $definition;
    }

    public function apply(Builder $query, Request $request, string $dataset, array $allowed): void
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1|max:1000000', 'per_page' => 'sometimes|integer|min:1|max:100',
            'q' => 'sometimes|string|max:500', 'filter' => 'sometimes|array|max:30',
            'fields' => 'sometimes|string|max:2000', 'sort' => 'sometimes|string|max:500',
            'include' => 'sometimes|string|max:500',
            'period_start' => 'required_with:period_end|date_format:Y-m-d',
            'period_end' => 'required_with:period_start|date_format:Y-m-d|after_or_equal:period_start',
            'period_mode' => 'sometimes|in:overlap,start',
        ]);
        $known = ['page', 'per_page', 'q', 'filter', 'fields', 'sort', 'include'];
        if ($dataset === 'reports') {
            $known = array_merge($known, ['period_start', 'period_end', 'period_mode']);
        }
        abort_if(array_diff(array_keys($request->query()), $known), 422, 'Parameter tidak dikenal.');
        $table = $query->getModel()->getTable();
        foreach ($request->input('filter', []) as $field => $operations) {
            if ($dataset === 'reports' && in_array($field, ['indicator_id', 'team_id', 'work_unit_id', 'involvement_id'], true)) {
                $relationDataset = ['indicator_id' => 'indicators', 'team_id' => 'teams', 'work_unit_id' => 'work_units', 'involvement_id' => 'involvements'][$field];
                $relationAllowed = $request->attributes->get('ai_fields')[$relationDataset] ?? [];
                $this->field('id', $relationDataset, $relationAllowed);
                abort_unless(is_array($operations) && count($operations) === 1 && isset($operations['eq']) && is_scalar($operations['eq']) && ctype_digit((string) $operations['eq']), 422, 'Filter relasi memakai eq dan ID numerik.');
                $relation = Registry::REFERENCES[$relationDataset][1];
                $query->whereHas($relation, fn (Builder $q) => $q->whereKey($operations['eq']));

                continue;
            }
            [$type] = $this->field((string) $field, $dataset, $allowed);
            abort_unless(is_array($operations) && count($operations) <= 6, 422, 'Operator filter tidak sah.');
            foreach ($operations as $operator => $value) {
                abort_unless(in_array($operator, app(Registry::class)->operators($type), true), 422, 'Operator tidak tersedia untuk tipe kolom.');
                $column = $table.'.'.$field;
                if ($operator === 'in') {
                    $values = is_string($value) ? $this->csv($value) : $value;
                    abort_unless(is_array($values) && count($values) > 0 && count($values) <= 100, 422, 'Daftar filter tidak sah.');
                    $values = array_map(fn ($item) => $this->value($item, $type), $values);
                    $query->whereIn($column, $values);
                } elseif ($operator === 'is_null') {
                    abort_unless(in_array($value, ['true', 'false', '1', '0'], true), 422, 'is_null harus boolean.');
                    in_array($value, ['true', '1'], true) ? $query->whereNull($column) : $query->whereNotNull($column);
                } else {
                    $value = $this->value($value, $type);
                    $op = ['eq' => '=', 'gte' => '>=', 'lte' => '<=', 'contains' => 'like'][$operator];
                    if ($operator === 'contains') {
                        $query->whereRaw($query->getQuery()->getGrammar()->wrap($column)." LIKE ? ESCAPE '!'", ['%'.$this->literalLike((string) $value).'%']);
                    } else {
                        $query->where($column, $op, $value);
                    }
                }
            }
        }
        if ($request->filled('q')) {
            $search = array_filter($allowed, fn ($field) => in_array(app(Registry::class)->definitions()[$dataset][$field][0], ['string', 'text'], true));
            abort_unless($search, 403, 'Tidak ada kolom pencarian yang diizinkan.');
            $query->where(function (Builder $q) use ($search, $table, $request): void {
                foreach ($search as $field) {
                    $q->orWhereRaw($q->getQuery()->getGrammar()->wrap($table.'.'.$field)." LIKE ? ESCAPE '!'", ['%'.$this->literalLike($request->string('q')->toString()).'%']);
                }
            });
        }
        if ($dataset === 'reports' && $request->filled('period_start')) {
            $this->field('when', 'reports', $allowed);
            $query->where('when', '<=', $request->input('period_end'));
            if ($request->input('period_mode', 'overlap') === 'start') {
                $query->where('when', '>=', $request->input('period_start'));
            } else {
                $this->field('tanggal_selesai', 'reports', $allowed);
                $query->whereRaw('COALESCE(tanggal_selesai, '.$query->getQuery()->getGrammar()->wrap('when').') >= ?', [$request->input('period_start')]);
            }
        }
        foreach ($this->csv($request->input('sort', '')) as $sort) {
            $field = ltrim($sort, '-');
            if ($field !== 'id' || $dataset !== 'reports') {
                [$type] = $this->field($field, $dataset, $allowed);
                abort_if($type === 'array', 422, 'Kolom array tidak dapat diurutkan.');
            }
            $query->orderBy($table.'.'.$field, str_starts_with($sort, '-') ? 'desc' : 'asc');
        }
        $query->orderBy($table.'.id');
    }

    private function value(mixed $value, string $type): string|int|float|bool
    {
        abort_unless(is_scalar($value) && strlen((string) $value) <= 2000, 422, 'Nilai filter tidak sah.');
        if ($type === 'boolean' && in_array($value, ['true', 'false'], true)) {
            $value = $value === 'true';
        }
        $rule = match ($type) {
            'integer' => 'integer', 'date' => 'date_format:Y-m-d', 'datetime' => 'date', 'boolean' => 'boolean', default => 'string',
        };
        validator(['value' => $value], ['value' => $rule])->validate();

        return match ($type) {
            'boolean' => (bool) $value,
            'datetime' => Carbon::parse((string) $value, config('app.timezone'))->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            default => $value,
        };
    }

    private function literalLike(string $text): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $text);
    }
}
