<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']); // simula portador de token
        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_m2m_ping_with_client_credentials(): void
    {
        Passport::actingAsClient(
            Client::factory()->create(),
            ['servers:read'] // exemplo de escopo
        );

        $this->getJson('/api/m2m/ping')->assertOk();
    }
}
