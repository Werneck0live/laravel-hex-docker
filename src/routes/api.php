<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\HealthcheckController;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;

// healthcheck existente
Route::get('/healthz', [HealthcheckController::class, 'index'])->name('healthz');

// password grant
Route::post('/auth/login',   [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// protegidas por user token
Route::middleware('auth:api')->group(function () {
    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// exemplo de rota M2M (client credentials):
Route::get('/m2m/ping', fn () => ['pong' => true])
    ->middleware(EnsureClientIsResourceOwner::class);
