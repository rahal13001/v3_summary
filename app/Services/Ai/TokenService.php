<?php

namespace App\Services\Ai;

use App\Models\AiAccessGrant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TokenService
{
    public function issue(User $user, string $name, array $datasets, array $fields, string $scope, int $days): string
    {
        if (! $user->status || $user->trashed() || ! $user->checkPermissionTo('use_ai_api', 'web')) {
            throw new AuthorizationException;
        }
        validator(compact('name', 'datasets', 'fields', 'scope', 'days'), [
            'name' => 'required|string|max:100', 'datasets' => 'required|array|min:1',
            'datasets.*' => 'required|string|distinct', 'fields' => 'required|array',
            'scope' => 'required|in:account,own_related', 'days' => 'required|integer|in:7,30,90',
        ])->validate();
        $access = app(Access::class);
        $definitions = app(Registry::class)->definitions();
        if (! in_array('reports', $datasets, true) || array_diff(array_keys($fields), $datasets)) {
            throw ValidationException::withMessages(['datasets' => 'Laporan wajib dipilih; kolom harus sesuai dataset.']);
        }
        foreach ($datasets as $dataset) {
            if (! isset($definitions[$dataset]) || ! $access->accountAllows($user, $dataset)
                || ! isset($fields[$dataset]) || ! is_array($fields[$dataset]) || $fields[$dataset] === []
                || count(array_filter($fields[$dataset], 'is_string')) !== count($fields[$dataset])
                || array_diff($fields[$dataset], array_keys($definitions[$dataset]))) {
                throw ValidationException::withMessages(['fields' => 'Dataset atau kolom tidak diizinkan: '.$dataset]);
            }
        }

        return DB::transaction(function () use ($user, $name, $datasets, $fields, $scope, $days): string {
            $token = $user->createToken($name, ['ai:read'], now()->addDays($days));
            AiAccessGrant::create([
                'personal_access_token_id' => $token->accessToken->id,
                'report_scope' => $scope, 'datasets' => $datasets, 'fields' => $fields,
            ]);

            return $token->plainTextToken;
        });
    }
}
