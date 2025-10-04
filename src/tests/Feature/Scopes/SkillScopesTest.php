<?php

namespace Tests\Feature\Scopes;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Passport\Passport;

class SkillScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_requires_scope_read(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['profile:read']); // escopo errado

        $this->getJson('/api/skills')
            ->assertStatus(403); // "Invalid scope(s) provided."
    }

    public function test_list_with_scope_read(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['skills:read']);

        $this->getJson('/api/skills')->assertOk();
    }

    public function test_store_requires_scope_write(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['skills:read']); // sem write

        $this->postJson('/api/skills', [
            'name' => 'Kubernetes', 'level' => 4,
            'years_experience' => 3, 'tags' => ['devops']
        ])->assertStatus(403);
    }

    public function test_store_with_scope_write(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['skills:write']);

        $this->postJson('/api/skills', [
            'name' => 'Kubernetes', 'level' => 4,
            'years_experience' => 3, 'tags' => ['devops']
        ])->assertCreated()->assertJsonPath('data.name', 'Kubernetes');
    }
}
