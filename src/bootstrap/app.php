<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

use App\Http\Middleware\RequireScopes;
use App\Http\Middleware\RequireAnyScope;

use App\Http\Middleware\JsonResponse;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', \App\Http\Middleware\JsonResponse::class);

        $middleware->alias([
            'scopes' => \App\Http\Middleware\RequireScopes::class,   // exige TODOS
            'scope'  => \App\Http\Middleware\RequireAnyScope::class, // exige ALGUM
        ]);

        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Helpers p/ payload de erro
        $makePayload = function (\Throwable $e, int $status) {
            $msg = trim((string) $e->getMessage()) ?: (\Symfony\Component\HttpFoundation\Response::$statusTexts[$status] ?? 'Error');
            $payload = [
                'ok'     => false,
                'status' => $status,
                'error'  => class_basename($e),
                'message' => $msg,
            ];
            if (config('app.debug')) {
                $payload['file'] = $e->getFile();
                $payload['line'] = $e->getLine();
            }
            return $payload;
        };

        // Só converte para JSON se for /api/*
        $forApi = fn($request) => $request->is('api/*');

        // 401 - não autenticado
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 401), 401);
            }
        });

        // 403 - proibido
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 403), 403);
            }
        });

        // 422 - validação
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($forApi) {
            if ($forApi($request)) {
                return response()->json([
                    'ok'     => false,
                    'status' => 422,
                    'error'  => 'ValidationException',
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 404 - modelo não encontrado
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 404), 404);
            }
        });

        // 404 - rota não encontrada
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 404), 404);
            }
        });

        // 405 - método não permitido
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 405), 405);
            }
        });

        // 429 - rate limit
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 429), 429);
            }
        });

        // HttpException genérica (inclui abort())
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, $e->getStatusCode()), $e->getStatusCode());
            }
        });

        // Fallback 500
        $exceptions->render(function (\Throwable $e, $request) use ($makePayload, $forApi) {
            if ($forApi($request)) {
                return response()->json($makePayload($e, 500), 500);
            }
        });
    })
    ->create();
