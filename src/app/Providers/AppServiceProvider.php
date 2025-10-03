<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\CarbonInterval;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Hexagonal: porta -> adapter
        $this->app->bind(
            \App\Domain\Contracts\AuthService::class,
            \App\Infrastructure\Auth\PassportAuthService::class
        );
    }

    public function boot(): void
    {
        // 🔑 OBRIGATÓRIO no Passport atual para liberar o Password Grant
        Passport::enablePasswordGrant();

        // (opcional) TTLs bonitos
        Passport::tokensExpireIn(CarbonInterval::hours(1));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    }
}
