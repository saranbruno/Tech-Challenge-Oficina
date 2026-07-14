<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiFoundationTest extends TestCase
{
    public function test_unknown_api_route_returns_the_standard_error_envelope(): void
    {
        $response = $this->getJson('/api/unknown');

        $response
            ->assertNotFound()
            ->assertExactJson([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'The route api/unknown could not be found.',
                ],
            ]);
    }

    public function test_no_domain_endpoints_are_exposed_yet(): void
    {
        $apiRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'));

        $this->assertCount(0, $apiRoutes);
    }
}
