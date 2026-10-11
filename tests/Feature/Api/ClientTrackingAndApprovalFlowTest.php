<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientTrackingAndApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_tracks_only_own_order_with_document_and_random_token(): void
    {
        [$token, $orderId] = $this->createOrder();

        $this->postJson('/api/client/service-orders/tracking', [
            'customer_document' => '529.982.247-25',
            'tracking_token' => $token,
        ])->assertOk()
            ->assertJsonPath('data.service_order_id', $orderId)
            ->assertJsonPath('data.status', 'received')
            ->assertJsonMissingPath('data.customer_id')
            ->assertJsonMissingPath('data.vehicle_id')
            ->assertJsonMissingPath('data.tracking_token');

        $this->postJson('/api/client/service-orders/tracking', [
            'customer_document' => '52998224725',
            'tracking_token' => str_repeat('a', 64),
        ])->assertNotFound();

        $this->postJson('/api/client/service-orders/tracking', [
            'customer_document' => '04252011000110',
            'tracking_token' => $token,
        ])->assertNotFound();
    }

    public function test_dedicated_status_queries_are_minimal_and_secure(): void
    {
        [$token, $orderId, $adminToken] = $this->createOrder();

        $this->withToken($adminToken)
            ->getJson("/api/admin/service-orders/{$orderId}/status")
            ->assertOk()
            ->assertJsonPath('data.service_order_id', $orderId)
            ->assertJsonPath('data.status', 'received')
            ->assertJsonPath('data.status_label', 'Recebida')
            ->assertJsonStructure(['data' => ['service_order_id', 'status', 'status_label', 'last_transition_at']])
            ->assertJsonMissingPath('data.customer_id')
            ->assertJsonMissingPath('data.total_amount');

        $this->postJson('/api/client/service-orders/status', [
            'customer_document' => '529.982.247-25',
            'tracking_token' => $token,
        ])->assertOk()
            ->assertJsonPath('data.service_order_id', $orderId)
            ->assertJsonPath('data.status', 'received')
            ->assertJsonPath('data.status_label', 'Recebida')
            ->assertJsonMissingPath('data.customer_id')
            ->assertJsonMissingPath('data.total_amount');

        $this->postJson('/api/client/service-orders/status', [
            'customer_document' => '529.982.247-25',
            'tracking_token' => str_repeat('a', 64),
        ])->assertNotFound();
    }

    public function test_dedicated_status_queries_support_all_service_order_states(): void
    {
        [, $orderId, $adminToken] = $this->createOrder();
        $states = [
            'received' => ['received_at', 'Recebida'],
            'in_diagnosis' => ['diagnosis_started_at', 'Em diagnóstico'],
            'awaiting_approval' => ['awaiting_approval_at', 'Aguardando aprovação'],
            'in_execution' => ['execution_started_at', 'Em execução'],
            'finalized' => ['finalized_at', 'Finalizada'],
            'delivered' => ['delivered_at', 'Entregue'],
            'cancelled' => ['cancelled_at', 'Cancelada'],
        ];

        foreach ($states as $state => [$timestampColumn, $label]) {
            $timestamp = now()->subMinutes(count($states));
            DB::table('service_orders')->where('id', $orderId)->update([
                'status' => $state,
                $timestampColumn => $timestamp,
            ]);

            $this->withToken($adminToken)
                ->getJson("/api/admin/service-orders/{$orderId}/status")
                ->assertOk()
                ->assertJsonPath('data.status', $state)
                ->assertJsonPath('data.status_label', $label)
                ->assertJsonPath('data.last_transition_at', $timestamp->toAtomString());
        }
    }

    public function test_additional_repairs_recalculate_budget_before_explicit_approval(): void
    {
        [$trackingToken, $orderId, $adminToken] = $this->createOrder();
        $additionalServiceId = $this->createService('Alinhamento', 8000);

        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$orderId}/diagnosis/start")->assertOk();
        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$orderId}/diagnosis/complete")->assertOk();
        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$orderId}/additional-repairs", [
            'services' => [['service_id' => $additionalServiceId, 'quantity' => 2]],
        ])->assertOk()
            ->assertJsonPath('data.status', 'awaiting_approval')
            ->assertJsonPath('data.total_amount', 31000);

        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_execution');
        $this->assertDatabaseHas('service_orders', ['id' => $orderId, 'status' => 'in_execution']);
        $this->assertDatabaseHas('service_order_services', [
            'service_order_id' => $orderId,
            'service_id' => $additionalServiceId,
            'unit_price_snapshot' => 8000,
        ]);

        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '52998224725',
            'tracking_token' => $trackingToken,
        ])
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');
    }

    public function test_order_can_be_cancelled_before_execution_and_not_after_approval(): void
    {
        [, $cancelledOrderId, $adminToken] = $this->createOrder();

        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$cancelledOrderId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        [$approvedTrackingToken, $approvedOrderId] = $this->createOrder($adminToken, 'BRA2E19', '04252011000110');
        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$approvedOrderId}/diagnosis/start")->assertOk();
        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$approvedOrderId}/diagnosis/complete")->assertOk();
        $this->postJson('/api/client/service-orders/approve', [
            'customer_document' => '04252011000110',
            'tracking_token' => $approvedTrackingToken,
        ])->assertOk();
        $this->withToken($adminToken)->postJson("/api/admin/service-orders/{$approvedOrderId}/cancel")
            ->assertConflict();
    }

    private function createOrder(
        ?string $adminToken = null,
        string $plate = 'ABC1D23',
        string $document = '52998224725',
    ): array {
        $adminToken ??= $this->adminToken();
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
        $serviceId = $this->createService('Diagnostico '.$plate, 15000);
        $response = $this->withToken($adminToken)->postJson('/api/admin/service-orders', [
            'customer_document' => $document,
            'vehicle_id' => $vehicleId,
            'services' => [['service_id' => $serviceId, 'quantity' => 1]],
            'inventory_items' => [],
        ])->assertCreated();

        return [$response->json('data.tracking_token'), $response->json('data.id'), $adminToken];
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

    private function createService(string $name, int $unitPrice): int
    {
        return DB::table('services')->insertGetId([
            'name' => $name,
            'unit_price' => $unitPrice,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
