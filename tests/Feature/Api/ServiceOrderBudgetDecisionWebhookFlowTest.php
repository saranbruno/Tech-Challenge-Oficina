<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceOrderBudgetDecisionWebhookFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.service_order_webhook.secret' => 'local-webhook-secret',
            'services.service_order_webhook.timestamp_tolerance_seconds' => 0,
        ]);
    }

    public function test_approved_decision_starts_execution_and_decrements_stock_once(): void
    {
        [$orderId, $inventoryItemId] = $this->createOrderAwaitingApproval(true);

        $this->postDecision($orderId, 'approved')
            ->assertOk()
            ->assertJsonPath('data.service_order_id', $orderId)
            ->assertJsonPath('data.status', 'in_execution');
        $this->postDecision($orderId, 'approved')
            ->assertOk()
            ->assertJsonPath('data.status', 'in_execution');

        $this->assertDatabaseHas('service_orders', ['id' => $orderId, 'status' => 'in_execution']);
        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItemId, 'quantity_available' => 1]);
        self::assertSame(1, DB::table('stock_movements')->where('service_order_id', $orderId)->count());
    }

    public function test_rejected_decision_cancels_order_without_changing_stock(): void
    {
        [$orderId, $inventoryItemId] = $this->createOrderAwaitingApproval(true);

        $this->postDecision($orderId, 'rejected')
            ->assertOk()
            ->assertJsonPath('data.service_order_id', $orderId)
            ->assertJsonPath('data.status', 'cancelled');
        $this->postDecision($orderId, 'rejected')
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('service_orders', ['id' => $orderId, 'status' => 'cancelled']);
        $this->assertDatabaseHas('inventory_items', ['id' => $inventoryItemId, 'quantity_available' => 2]);
        self::assertSame(0, DB::table('stock_movements')->where('service_order_id', $orderId)->count());
    }

    public function test_conflicting_decision_is_rejected_after_a_terminal_decision(): void
    {
        [$orderId] = $this->createOrderAwaitingApproval(false);

        $this->postDecision($orderId, 'rejected')->assertOk();
        $this->postDecision($orderId, 'approved')
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');
    }

    private function postDecision(int $orderId, string $decision): \Illuminate\Testing\TestResponse
    {
        $payload = [
            'service_order_id' => $orderId,
            'decision' => $decision,
            'occurred_at' => (new DateTimeImmutable)->format(DATE_ATOM),
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->call(
            'POST',
            '/api/webhooks/service-orders/budget-decision',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => 'sha256='.hash_hmac('sha256', $body, 'local-webhook-secret'),
            ],
            content: $body,
        );
    }

    private function createOrderAwaitingApproval(bool $withInventory): array
    {
        $adminToken = $this->adminToken();
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente Webhook',
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
            'name' => 'Diagnostico Webhook',
            'unit_price' => 15000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inventoryItemId = DB::table('inventory_items')->insertGetId([
            'name' => 'Filtro Webhook',
            'type' => 'part',
            'unit_price' => 2500,
            'quantity_available' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $response = $this->withToken($adminToken)->postJson('/api/admin/service-orders', [
            'customer_document' => '52998224725',
            'vehicle_id' => $vehicleId,
            'services' => [['service_id' => $serviceId, 'quantity' => 1]],
            'inventory_items' => $withInventory
                ? [['inventory_item_id' => $inventoryItemId, 'quantity' => 1]]
                : [],
        ])->assertCreated();
        $orderId = $response->json('data.id');

        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$orderId}/diagnosis/start")
            ->assertOk();
        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$orderId}/diagnosis/complete")
            ->assertOk();

        return [$orderId, $inventoryItemId];
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
