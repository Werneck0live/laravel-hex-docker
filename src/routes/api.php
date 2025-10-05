<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthcheckController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SkillController;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;

// health
Route::get('/healthz', [HealthcheckController::class, 'index'])->name('healthz');

// auth
Route::post('/auth/login',   [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // skills: read
    Route::get('/skills',  [SkillController::class, 'index'])
        ->middleware('scopes:skills:read');

    // skills: write (vamos criar o "store" no controller)
    Route::post('/skills', [SkillController::class, 'store'])
        ->middleware('scopes:skills:write');
});

// Exemplo M2M (client credentials)
Route::get('/m2m/ping', fn () => ['pong' => true])
    ->middleware(EnsureClientIsResourceOwner::class);

    Route::get('/dev/oauth-clients', function () {
    abort_unless(app()->environment('local'), 403, 'forbidden');

    return response()->json([
        'passwordClientId'     => env('PASSPORT_PASSWORD_CLIENT_ID'),
        'passwordClientSecret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
        
        'm2mClientId'          => env('PASSPORT_M2M_CLIENT_ID'),
        'm2mClientSecret'      => env('PASSPORT_M2M_CLIENT_SECRET'),
    ]);
});
