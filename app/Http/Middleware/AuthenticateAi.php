<?php

namespace App\Http\Middleware;

use App\Models\AiAccessGrant;
use App\Models\User;
use App\Services\Ai\Access;
use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateAi
{
    public function handle(Request $request, Closure $next): Response
    {
        $started = microtime(true);
        $id = (string) Str::uuid();
        $request->attributes->set('ai_request_id', $id);
        try {
            abort_unless(config('ai.enabled'), 503, 'API AI belum diaktifkan.');
            $token = $request->bearerToken() ? PersonalAccessToken::findToken($request->bearerToken()) : null;
            abort_unless($token && (! $token->expires_at || $token->expires_at->isFuture()), 401, 'Token tidak sah atau kedaluwarsa.');
            abort_unless(($token->abilities ?? []) === ['ai:read'], 403, 'Token AI khusus diperlukan.');
            $grant = AiAccessGrant::where('personal_access_token_id', $token->id)->first();
            abort_unless($grant && in_array($grant->report_scope, ['account', 'own_related'], true), 403, 'Izin token AI tidak tersedia.');
            $user = $token->tokenable;
            abort_unless($user instanceof User && ! $user->trashed() && (bool) $user->status, 403, 'Akun tidak aktif.');
            abort_unless($user->checkPermissionTo('use_ai_api', 'web'), 403, 'Akses AI tidak diizinkan.');
            $user->withAccessToken($token);
            $request->setUserResolver(fn () => $user);
            $fields = app(Access::class)->effective($user, $grant);
            abort_unless(isset($fields['reports']), 403, 'Izin baca laporan tidak tersedia.');
            $request->attributes->set('ai_grant', $grant);
            $request->attributes->set('ai_fields', $fields);
            foreach (['ai:token:'.$token->id => (int) config('ai.per_token_per_minute'), 'ai:user:'.$user->id => (int) config('ai.per_user_per_minute')] as $key => $limit) {
                if (RateLimiter::tooManyAttempts($key, $limit)) {
                    abort(429, 'Batas permintaan tercapai.', ['Retry-After' => (string) RateLimiter::availableIn($key)]);
                }
                RateLimiter::hit($key, 60);
            }
            $token->forceFill(['last_used_at' => now()])->save();
            $response = $next($request);
        } catch (Throwable $exception) {
            $response = app(ExceptionHandler::class)->render($request, $exception);
        }
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Request-ID', $id);
        Log::channel('ai')->info('ai.request', [
            'request_id' => $id, 'token_id' => $token->id ?? null, 'user_id' => $user->id ?? null,
            'endpoint' => $request->route()?->uri(), 'status' => $response->getStatusCode(),
            'duration_ms' => round((microtime(true) - $started) * 1000),
            'result_count' => $request->attributes->get('ai_result_count', 0),
        ]);

        return $response;
    }
}
