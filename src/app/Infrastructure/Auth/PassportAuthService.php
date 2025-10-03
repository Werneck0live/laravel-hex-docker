<?php

namespace App\Infrastructure\Auth;

use App\Domain\Contracts\AuthService;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class PassportAuthService implements AuthService
{
    public function loginWithPassword(string $email, string $password, string $scope = ''): array
    {
        $base = rtrim(env('OAUTH_BASE_URL', config('app.url')), '/');
        $res = Http::asForm()->post($base.'/oauth/token', [
            'grant_type'    => 'password',
            'client_id'     => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'username'      => $email,
            'password'      => $password,
            'scope'         => $scope ?: env('PASSPORT_PASSWORD_DEFAULT_SCOPE', ''),
        ]);

        abort_if($res->failed(), 401, 'invalid_credentials');
        return $res->json();
    }

    public function refreshToken(string $refreshToken, string $scope = ''): array
    {
        $base = rtrim(env('OAUTH_BASE_URL', config('app.url')), '/');
        $res = Http::asForm()->post($base.'/oauth/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id'     => env('PASSPORT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
            'scope'         => $scope ?: env('PASSPORT_PASSWORD_DEFAULT_SCOPE', ''),
        ]);

        abort_if($res->failed(), $res->status(), 'invalid_refresh_token');
        return $res->json();
    }

    public function logoutUser(User $user): void
    {
        $tokenId = optional($user->token())->id;
        if ($tokenId) {
            $token = Passport::token()->find($tokenId);
            if ($token instanceof Token) {
                $token->revoke();
                $token->refreshToken?->revoke();
            }
        }
    }
}
