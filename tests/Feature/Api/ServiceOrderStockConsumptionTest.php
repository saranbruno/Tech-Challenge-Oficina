<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceOrderStockConsumptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_consumes_inventory_and_records_movements_once(): void
    {
        [$trackingToken, $orderId, $adminToken, $partId, $supplyId] = $this->createOrder(5, 8, 2, 3);

        $this->prepareForApproval($adminToken, $orderId);

        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])->assertOk()
            ->assertJsonPath('data.status', 'in_execution');

        $this->assertDatabaseHas('inventory_items', ['id' => $partId, 'quantity_available' => 3]);
        $this->assertDatabaseHas('inventory_items', ['id' => $supplyId, 'quantity_available' => 5]);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $partId,
            'service_order_id' => $orderId,
            'admin_user_id' => null,
            'type' => 'service_order_consumption',
            'quantity_change' => -2,
            'quantity_before' => 5,
            'quantity_after' => 3,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $supplyId,
            'service_order_id' => $orderId,
            'type' => 'service_order_consumption',
            'quantity_change' => -3,
            'quantity_before' => 8,
            'quantity_after' => 5,
        ]);

        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');

        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('inventory_items', ['id' => $partId, 'quantity_available' => 3]);
        $this->assertDatabaseHas('inventory_items', ['id' => $supplyId, 'quantity_available' => 5]);
    }

    public function test_insufficient_stock_rolls_back_every_item_and_keeps_order_awaiting_approval(): void
    {
        [$trackingToken, $orderId, $adminToken, $partId, $supplyId] = $this->createOrder(5, 2, 2, 3);

        $this->prepareForApproval($adminToken, $orderId);

        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])->assertConflict()
            ->assertJsonPath('error.code', 'insufficient_inventory_stock');

        $this->assertDatabaseHas('service_orders', [
            'id' => $orderId,
            'status' => 'awaiting_approval',
            'execution_started_at' => null,
        ]);
        $this->assertDatabaseHas('inventory_items', ['id' => $partId, 'quantity_available' => 5]);
        $this->assertDatabaseHas('inventory_items', ['id' => $supplyId, 'quantity_available' => 2]);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    private function prepareForApproval(string $adminToken, int $orderId): void
    {
        $this->withToken($adminToken)
            ->postJson("/api/admin/service-orders/{$orderId}/diagnosis/start")
            ->assertOk();
        $this->withToken($adminToken)
            ->postJson("/api/admin/service-orders/{$orderId}/diagnosis/complete")
            ->assertOk();
    }

    private function createOrder(
        int $partStock,
        int $supplyStock,
        int $partQuantity,
        int $supplyQuantity,
    ): array {
        $adminToken = $this->adminToken();
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente Estoque',
            'document' => '52998224725',
            'document_type' => 'cpf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('vehicles')->insertGetId([
            'customer_id' => $customerId,
            'license_plate' => 'ABC1D23',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'year' => 2020,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $serviceId = DB::table('services')->insertGetId([
            'name' => 'Servico com estoque',
            'unit_price' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $partId = $this->createInventoryItem('Peca', 'part', $partStock);
        $supplyId = $this->createInventoryItem('Insumo', 'supply', $supplyStock);
        $response = $this->withToken($adminToken)->postJson('/api/admin/service-orders', [
            'customer_document' => '52998224725',
            'vehicle_id' => $vehicleId,
            'services' => [['service_id' => $serviceId, 'quantity' => 1]],
            'inventory_items' => [
                ['inventory_item_id' => $partId, 'quantity' => $partQuantity],
                ['inventory_item_id' => $supplyId, 'quantity' => $supplyQuantity],
            ],
        ])->assertCreated();

        return [
            $response->json('data.tracking_token'),
            $response->json('data.id'),
            $adminToken,
            $partId,
            $supplyId,
        ];
    }

    private function createInventoryItem(string $name, string $type, int $stock): int
    {
        return DB::table('inventory_items')->insertGetId([
            'name' => $name,
            'type' => $type,
            'unit_price' => 2500,
            'quantity_available' => $stock,
            'created_at' => now(),
            'updated_at' => now(),
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
