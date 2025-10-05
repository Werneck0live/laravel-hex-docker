<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequireAnyScope
{
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $user = $request->user();
        if (!$user) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        $token = $user->token();
        $granted = (array) ($token?->scopes ?? []);

        if (in_array('*', $granted, true)) {
            return $next($request);
        }

        foreach ($scopes as $scope) {
            if ($user->tokenCan($scope)) {
                return $next($request);
            }
        }

        throw new HttpException(403, 'Insufficient scope.');
    }
}
