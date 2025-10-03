<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthcheckController;

Route::get('/healthz', [HealthcheckController::class, 'index'])->name('healthz');
