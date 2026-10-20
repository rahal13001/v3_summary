<?php

use App\Http\Controllers\API\AiController;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\AuthenticateAi;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('ai/v1')->middleware(AuthenticateAi::class)->group(function (): void {
    Route::get('capabilities', [AiController::class, 'capabilities']);
    Route::get('schema', [AiController::class, 'schema']);
    Route::get('reports', [AiController::class, 'reports']);
    Route::get('reports/{id}', [AiController::class, 'show'])->whereNumber('id');
    Route::get('reports/{id}/followers', [AiController::class, 'followers'])->whereNumber('id');
    Route::get('references/{dataset}', [AiController::class, 'references']);
    Route::get('evaluations', [AiController::class, 'evaluations']);
});

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->name('logout');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', EnsureUserIsActive::class]);
