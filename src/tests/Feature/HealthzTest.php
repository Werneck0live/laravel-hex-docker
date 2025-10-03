<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthzTest extends TestCase
{
    public function test_returns_ok_on_healthz(): void
    {
        $this->getJson('/api/healthz')
            ->assertOk()
            ->assertJsonStructure(['status', 'app', 'environment', 'timestamp']);
    }
}
