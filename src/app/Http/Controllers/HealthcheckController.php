<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthcheckController extends Controller
{
    public function index(): JsonResponse
    {
        $payload = [
            'status'      => 'ok',
            'app'         => config('app.name'),
            'environment' => app()->environment(),
            'timestamp'   => now()->toIso8601String(),
        ];

        if (filter_var(env('HEALTHZ_DEEP', false), FILTER_VALIDATE_BOOL)) {
            try {
                DB::connection()->getPdo();
                $payload['db'] = 'ok';
            } catch (\Throwable $e) {
                $payload['db'] = 'error';
                $payload['db_error'] = $e->getMessage();
            }
        }

        return response()->json($payload);
    }
}
