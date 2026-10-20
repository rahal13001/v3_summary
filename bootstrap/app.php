<?php

use App\Http\Middleware\IsolateAiTokens;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(IsolateAiTokens::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/ai/v1', 'api/ai/v1/*')) {
                return null;
            }
            $status = match (true) {
                $exception instanceof ValidationException => 422,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof AuthenticationException => 401,
                $exception instanceof ModelNotFoundException => 404,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };
            $codes = [401 => 'UNAUTHENTICATED', 403 => 'FORBIDDEN', 404 => 'NOT_FOUND', 405 => 'METHOD_NOT_ALLOWED', 422 => 'VALIDATION_ERROR', 429 => 'RATE_LIMITED', 503 => 'API_DISABLED'];

            return response()->json(['error' => [
                'code' => $codes[$status] ?? 'INTERNAL_ERROR',
                'message' => $status >= 500 ? 'Layanan API tidak tersedia.' : ($status === 422 ? 'Parameter tidak sah.' : ($exception->getMessage() ?: 'Permintaan ditolak.')),
                'request_id' => $request->attributes->get('ai_request_id', (string) Str::uuid()),
            ]], $status, $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [])
                ->header('Cache-Control', 'private, no-store');
        });
    })->create();
