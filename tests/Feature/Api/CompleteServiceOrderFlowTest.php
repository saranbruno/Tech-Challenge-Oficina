<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteServiceOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_service_order_flow_uses_real_api_and_postgresql(): void
    {
        $adminToken = $this->adminToken();
        $customerId = $this->withToken($adminToken)->postJson('/api/admin/customers', [
            'name' => 'Cliente Fluxo Completo',
            'document' => '529.982.247-25',
        ])->assertCreated()->json('data.id');
        $vehicleId = $this->withToken($adminToken)->postJson('/api/admin/vehicles', [
            'customer_id' => $customerId,
            'license_plate' => 'abc-1234',
            'brand' => 'Volkswagen',
            'model' => 'Gol',
            'year' => 2020,
        ])->assertCreated()->json('data.id');
        $serviceId = $this->withToken($adminToken)->postJson('/api/admin/services', [
            'name' => 'Troca de oleo',
            'unit_price' => 15000,
        ])->assertCreated()->json('data.id');
        $inventoryItemId = $this->withToken($adminToken)->postJson('/api/admin/inventory-items', [
            'name' => 'Filtro de oleo',
            'type' => 'part',
            'unit_price' => 2500,
            'quantity_available' => 5,
        ])->assertCreated()->json('data.id');

        $creation = $this->withToken($adminToken)->postJson('/api/admin/service-orders', [
            'customer_document' => '52998224725',
            'vehicle_id' => $vehicleId,
            'services' => [['service_id' => $serviceId, 'quantity' => 1]],
            'inventory_items' => [['inventory_item_id' => $inventoryItemId, 'quantity' => 2]],
            'total_amount' => 1,
        ])->assertCreated()
            ->assertJsonPath('data.status', 'received')
            ->assertJsonPath('data.total_amount', 20000);
        $serviceOrderId = $creation->json('data.id');
        $trackingToken = $creation->json('data.tracking_token');

        $this->postJson('/api/client/service-orders/tracking', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])->assertOk()->assertJsonPath('data.status', 'received');

        $this->withToken($adminToken)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/start")
            ->assertOk()->assertJsonPath('data.status', 'in_diagnosis');
        $this->withToken($adminToken)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/complete")
            ->assertOk()->assertJsonPath('data.status', 'awaiting_approval');

        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '529.982.247-25',
            'tracking_token' => $trackingToken,
        ])->assertOk()->assertJsonPath('data.status', 'in_execution');
        $this->assertDatabaseHas('inventory_items', [
            'id' => $inventoryItemId,
            'quantity_available' => 3,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $inventoryItemId,
            'service_order_id' => $serviceOrderId,
            'type' => 'service_order_consumption',
            'quantity_change' => -2,
        ]);

        $this->withToken($adminToken)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/finalize")
            ->assertOk()->assertJsonPath('data.status', 'finalized');
        $this->withToken($adminToken)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/deliver")
            ->assertOk()->assertJsonPath('data.status', 'delivered');

        $this->postJson('/api/client/service-orders/tracking', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])->assertOk()->assertJsonPath('data.status', 'delivered');
        $this->withToken($adminToken)
            ->getJson("/api/admin/service-orders/{$serviceOrderId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('data.total_amount', 20000);
        $this->withToken($adminToken)
            ->getJson("/api/admin/service-orders-metrics/execution-time?service_id={$serviceId}")
            ->assertOk()
            ->assertJsonPath('data.eligible_orders', 1);
    }

    private function adminToken(): string
    {
        User::factory()->create([
            'email' => 'complete-flow@example.com',
            'password' => 'secure-password',
        ]);

        return $this->postJson('/api/admin/auth/login', [
            'email' => 'complete-flow@example.com',
            'password' => 'secure-password',
        ])->assertOk()->json('access_token');
    }
}
