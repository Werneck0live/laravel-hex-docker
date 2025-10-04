<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\CarbonInterval;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth
        $this->app->bind(
            \App\Domain\Contracts\AuthService::class,
            \App\Infrastructure\Auth\PassportAuthService::class
        );

        // Skills
        $this->app->bind(
            \App\Domain\Contracts\SkillRepository::class,
            \App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSkillRepository::class
        );
        // (apenas se você já criou o Writer)
        if (interface_exists(\App\Domain\Contracts\SkillWriter::class)) {
            $this->app->bind(
                \App\Domain\Contracts\SkillWriter::class,
                \App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSkillWriter::class
            );
        }
    }




    public function boot(): void
    {
        Passport::enablePasswordGrant();

        // Escopos (abilities)
        Passport::tokensCan([
            'skills:read'  => 'Listar habilidades',
            'skills:write' => 'Criar/editar/excluir habilidades',
        ]);

        Passport::tokensExpireIn(CarbonInterval::hours(1));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    }
}
