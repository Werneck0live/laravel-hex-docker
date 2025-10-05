<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequireScopes
{
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $user = $request->user(); // guard 'api' do Passport
        if (!$user) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        $token = $user->token();
        $granted = (array) ($token?->scopes ?? []);

        // aceita wildcard
        if (in_array('*', $granted, true)) {
            return $next($request);
        }

        // precisa ter TODOS os escopos exigidos
        foreach ($scopes as $scope) {
            if (!$user->tokenCan($scope)) {
                throw new HttpException(403, 'Insufficient scope.');
            }
        }

        return $next($request);
    }
}
