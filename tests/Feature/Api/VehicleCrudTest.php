<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\VehicleModel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/vehicles')->assertUnauthorized();
        $this->postJson('/api/admin/vehicles', [])->assertUnauthorized();
    }

    public function test_admin_can_create_list_show_update_and_delete_a_vehicle(): void
    {
        $customer = $this->customer();
        $otherCustomer = $this->customer('04252011000110');
        $token = $this->adminToken();

        $created = $this->withToken($token)->postJson('/api/admin/vehicles', [
            'customer_id' => $customer->getKey(),
            'license_plate' => 'abc-1234',
            'brand' => 'Volkswagen',
            'model' => 'Gol',
            'year' => 2020,
        ])->assertCreated()
            ->assertJsonPath('data.customer_id', $customer->getKey())
            ->assertJsonPath('data.license_plate', 'ABC1234');

        $vehicleId = $created->json('data.id');

        $this->withToken($token)->getJson('/api/admin/vehicles')
            ->assertOk()
            ->assertJsonPath('data.0.id', $vehicleId)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->withToken($token)->getJson("/api/admin/vehicles/{$vehicleId}")
            ->assertOk()
            ->assertJsonPath('data.model', 'Gol');

        $this->withToken($token)->putJson("/api/admin/vehicles/{$vehicleId}", [
            'customer_id' => $otherCustomer->getKey(),
            'license_plate' => 'bra1e23',
            'brand' => 'Fiat',
            'model' => 'Pulse',
            'year' => 2025,
        ])->assertOk()
            ->assertJsonPath('data.customer_id', $otherCustomer->getKey())
            ->assertJsonPath('data.license_plate', 'BRA1E23');

        $this->withToken($token)->deleteJson("/api/admin/vehicles/{$vehicleId}")->assertNoContent();
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicleId]);
    }

    public function test_invalid_plate_and_required_fields_are_rejected(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->postJson('/api/admin/vehicles', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');

        $this->withToken($token)->postJson('/api/admin/vehicles', [
            'customer_id' => $this->customer()->getKey(),
            'license_plate' => 'INVALID',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'year' => 2020,
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'invalid_license_plate');
    }

    public function test_missing_customer_is_rejected(): void
    {
        $this->withToken($this->adminToken())->postJson('/api/admin/vehicles', [
            'customer_id' => 999999,
            'license_plate' => 'ABC1234',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'year' => 2020,
        ])->assertNotFound();
    }

    public function test_duplicate_plate_is_blocked_by_the_application(): void
    {
        $customer = $this->customer();
        $token = $this->adminToken();
        $payload = [
            'customer_id' => $customer->getKey(),
            'license_plate' => 'ABC1234',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'year' => 2020,
        ];

        $this->withToken($token)->postJson('/api/admin/vehicles', $payload)->assertCreated();
        $payload['license_plate'] = 'abc-1234';

        $this->withToken($token)->postJson('/api/admin/vehicles', $payload)
            ->assertConflict()
            ->assertJsonPath('error.code', 'duplicate_license_plate');
    }

    public function test_postgresql_enforces_plate_uniqueness(): void
    {
        $customer = $this->customer();
        $attributes = [
            'customer_id' => $customer->getKey(),
            'license_plate' => 'ABC1234',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'year' => 2020,
        ];
        VehicleModel::query()->create($attributes);

        $this->expectException(QueryException::class);
        VehicleModel::query()->create($attributes);
    }

    public function test_postgresql_enforces_customer_integrity(): void
    {
        $this->expectException(QueryException::class);
        VehicleModel::query()->create([
            'customer_id' => 999999,
            'license_plate' => 'BRA1E23',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'year' => 2020,
        ]);
    }

    private function customer(string $document = '52998224725'): CustomerModel
    {
        return CustomerModel::query()->create([
            'name' => 'Cliente',
            'document' => $document,
            'document_type' => strlen($document) === 11 ? 'cpf' : 'cnpj',
        ]);
    }

    private function adminToken(): string
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        return $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ])->json('access_token');
    }
}
