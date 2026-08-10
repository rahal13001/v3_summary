<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth:sanctum', EnsureUserIsActive::class])
    ->name('logout');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', EnsureUserIsActive::class]);
