<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_returns_ok_json(): void
    {
        $response = $this->getJson(route('health'));

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
        ]);
    }

    public function test_framework_up_endpoint_returns_successful_response(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
