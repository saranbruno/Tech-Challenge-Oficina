<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceOrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_requires_administrative_authentication(): void
    {
        $this->postJson('/api/admin/service-orders', [])->assertUnauthorized();
    }

    public function test_admin_creates_complete_order_with_server_calculated_budget_and_snapshots(): void
    {
        [$customerId, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        $serviceId = $this->createService('Troca de oleo', 15000);
        $partId = $this->createInventoryItem('Filtro', 'part', 2500, 10);
        $supplyId = $this->createInventoryItem('Oleo', 'supply', 4000, 20);

        $response = $this->withToken($this->adminToken())->postJson('/api/admin/service-orders', [
            'customer_document' => '529.982.247-25',
            'vehicle_id' => $vehicleId,
            'services' => [
                ['service_id' => $serviceId, 'quantity' => 2],
            ],
            'inventory_items' => [
                ['inventory_item_id' => $partId, 'quantity' => 1],
                ['inventory_item_id' => $supplyId, 'quantity' => 3],
            ],
            'total_amount' => 1,
        ])->assertCreated()
            ->assertJsonPath('data.customer_id', $customerId)
            ->assertJsonPath('data.status', 'received')
            ->assertJsonPath('data.services.0.subtotal', 30000)
            ->assertJsonPath('data.inventory_items.0.type', 'part')
            ->assertJsonPath('data.inventory_items.1.subtotal', 12000)
            ->assertJsonPath('data.total_amount', 44500);

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('service_orders', [
            'id' => $orderId,
            'total_amount' => 44500,
            'status' => 'received',
        ]);
        $this->assertDatabaseHas('service_order_inventory_items', [
            'service_order_id' => $orderId,
            'inventory_item_id' => $partId,
            'type_snapshot' => 'part',
            'quantity' => 1,
            'unit_price_snapshot' => 2500,
        ]);

        DB::table('services')->where('id', $serviceId)->update(['unit_price' => 99999]);
        DB::table('inventory_items')->where('id', $partId)->update(['unit_price' => 99999]);

        $this->assertDatabaseHas('service_order_services', [
            'service_order_id' => $orderId,
            'unit_price_snapshot' => 15000,
        ]);
        $this->assertDatabaseHas('service_order_inventory_items', [
            'service_order_id' => $orderId,
            'inventory_item_id' => $partId,
            'unit_price_snapshot' => 2500,
        ]);
        $this->assertDatabaseHas('inventory_items', ['id' => $partId, 'quantity_available' => 10]);
        $this->assertDatabaseHas('inventory_items', ['id' => $supplyId, 'quantity_available' => 20]);
    }

    public function test_order_can_be_created_without_inventory_items(): void
    {
        [, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        $serviceId = $this->createService('Diagnostico', 5000);

        $this->withToken($this->adminToken())->postJson('/api/admin/service-orders', [
            'customer_document' => '52998224725',
            'vehicle_id' => $vehicleId,
            'services' => [['service_id' => $serviceId, 'quantity' => 1]],
            'inventory_items' => [],
        ])->assertCreated()
            ->assertJsonPath('data.total_amount', 5000)
            ->assertJsonCount(0, 'data.inventory_items');
    }

    public function test_invalid_composition_is_rejected_without_partial_persistence(): void
    {
        [, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');

        $this->withToken($this->adminToken())->postJson('/api/admin/service-orders', [
            'customer_document' => '52998224725',
            'vehicle_id' => $vehicleId,
            'services' => [],
            'inventory_items' => [],
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');

        self::assertSame(0, DB::table('service_orders')->count());
    }

    public function test_vehicle_must_belong_to_identified_customer(): void
    {
        $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        [, $otherVehicleId] = $this->createCustomerAndVehicle('04252011000110', 'BRA2E19');
        $serviceId = $this->createService('Diagnostico', 5000);

        $this->withToken($this->adminToken())->postJson('/api/admin/service-orders', [
            'customer_document' => '52998224725',
            'vehicle_id' => $otherVehicleId,
            'services' => [['service_id' => $serviceId, 'quantity' => 1]],
            'inventory_items' => [],
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'vehicle_not_owned_by_customer');

        self::assertSame(0, DB::table('service_orders')->count());
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

    private function createCustomerAndVehicle(string $document, string $plate): array
    {
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente OS',
            'document' => $document,
            'document_type' => strlen($document) === 11 ? 'cpf' : 'cnpj',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('vehicles')->insertGetId([
            'customer_id' => $customerId,
            'license_plate' => $plate,
            'brand' => 'Marca',
            'model' => 'Modelo',
            'year' => 2020,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$customerId, $vehicleId];
    }

    private function createService(string $name, int $unitPrice): int
    {
        return DB::table('services')->insertGetId([
            'name' => $name,
            'unit_price' => $unitPrice,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createInventoryItem(
        string $name,
        string $type,
        int $unitPrice,
        int $quantity,
    ): int {
        return DB::table('inventory_items')->insertGetId([
            'name' => $name,
            'type' => $type,
            'unit_price' => $unitPrice,
            'quantity_available' => $quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
