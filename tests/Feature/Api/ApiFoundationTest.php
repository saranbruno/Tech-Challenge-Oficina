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

    public function test_only_implemented_endpoints_are_exposed(): void
    {
        $apiRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'))
            ->map(fn ($route) => $route->uri())
            ->sort()
            ->values()
            ->all();

        $this->assertSame([
            'api/admin/auth/login',
            'api/admin/auth/me',
            'api/admin/auth/refresh',
            'api/admin/customers',
            'api/admin/customers',
            'api/admin/customers/{customer}',
            'api/admin/customers/{customer}',
            'api/admin/customers/{customer}',
            'api/admin/services',
            'api/admin/services',
            'api/admin/services/{service}',
            'api/admin/services/{service}',
            'api/admin/services/{service}',
            'api/admin/vehicles',
            'api/admin/vehicles',
            'api/admin/vehicles/{vehicle}',
            'api/admin/vehicles/{vehicle}',
            'api/admin/vehicles/{vehicle}',
        ], $apiRoutes);
    }
}
