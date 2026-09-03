<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceOrderAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrative_operations_require_authentication(): void
    {
        $this->getJson('/api/admin/service-orders')->assertUnauthorized();
        $this->getJson('/api/admin/service-orders/1')->assertUnauthorized();
        $this->postJson('/api/admin/service-orders/1/finalize')->assertUnauthorized();
        $this->postJson('/api/admin/service-orders/1/deliver')->assertUnauthorized();
    }

    public function test_admin_lists_and_details_orders_with_complete_composition(): void
    {
        $firstId = $this->createServiceOrder('in_execution', 'ABC1D23');
        $secondId = $this->createServiceOrder('received', 'BRA2E19');
        $token = $this->adminToken();

        $this->withToken($token)
            ->getJson('/api/admin/service-orders?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $firstId)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);

        $this->withToken($token)
            ->getJson("/api/admin/service-orders/{$firstId}")
            ->assertOk()
            ->assertJsonPath('data.id', $firstId)
            ->assertJsonPath('data.status', 'in_execution')
            ->assertJsonPath('data.services.0.quantity', 2)
            ->assertJsonPath('data.inventory_items.0.quantity', 1)
            ->assertJsonPath('data.total_amount', 25000);
    }

    public function test_admin_queue_prioritizes_status_and_oldest_order_and_excludes_terminal_states(): void
    {
        $orders = [];
        foreach (['received', 'in_diagnosis', 'awaiting_approval', 'in_execution'] as $index => $status) {
            $orderId = $this->createServiceOrder($status, ['GHI7J89', 'JKL0M12', 'MNO3P45', 'PQR6S78'][$index]);
            DB::table('service_orders')->where('id', $orderId)->update([
                'received_at' => now()->subHours(20 - $index),
            ]);
            $orders[$status] = $orderId;
        }
        $oldestReceivedId = $this->createServiceOrder('received', 'STU9V01');
        DB::table('service_orders')->where('id', $oldestReceivedId)->update([
            'received_at' => now()->subHours(30),
        ]);
        $finalizedId = $this->createServiceOrder('finalized', 'VWX2Y34');
        $deliveredId = $this->createServiceOrder('delivered', 'YZA5B67');
        $cancelledId = $this->createServiceOrder('cancelled', 'BCD8E90');

        $response = $this->withToken($this->adminToken())
            ->getJson('/api/admin/service-orders?per_page=20')
            ->assertOk();

        self::assertSame([
            $orders['in_execution'],
            $orders['awaiting_approval'],
            $orders['in_diagnosis'],
            $oldestReceivedId,
            $orders['received'],
        ], array_column($response->json('data'), 'id'));
        self::assertNotContains($finalizedId, array_column($response->json('data'), 'id'));
        self::assertNotContains($deliveredId, array_column($response->json('data'), 'id'));
        self::assertNotContains($cancelledId, array_column($response->json('data'), 'id'));
    }

    public function test_admin_finalizes_and_delivers_order_with_persisted_instants(): void
    {
        $serviceOrderId = $this->createServiceOrder('in_execution');
        $token = $this->adminToken();

        $finalizedResponse = $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/finalize")
            ->assertOk()
            ->assertJsonPath('data.status', 'finalized');
        self::assertNotNull($finalizedResponse->json('data.finalized_at'));

        $deliveredResponse = $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/deliver")
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered');
        self::assertNotNull($deliveredResponse->json('data.delivered_at'));

        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrderId,
            'status' => 'delivered',
        ]);
        self::assertNotNull(DB::table('service_orders')->where('id', $serviceOrderId)->value('finalized_at'));
        self::assertNotNull(DB::table('service_orders')->where('id', $serviceOrderId)->value('delivered_at'));
    }

    public function test_invalid_or_repeated_transitions_are_rejected_without_replacing_instants(): void
    {
        $serviceOrderId = $this->createServiceOrder('received');
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/finalize")
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');
        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/deliver")
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');

        $executableId = $this->createServiceOrder('in_execution', 'DEF4G56');
        $this->withToken($token)->postJson("/api/admin/service-orders/{$executableId}/finalize")->assertOk();
        $firstFinalizedAt = DB::table('service_orders')->where('id', $executableId)->value('finalized_at');
        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$executableId}/finalize")
            ->assertConflict();
        self::assertEquals(
            $firstFinalizedAt,
            DB::table('service_orders')->where('id', $executableId)->value('finalized_at'),
        );
    }

    public function test_unknown_order_returns_not_found_for_every_administrative_operation(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->getJson('/api/admin/service-orders/999999')->assertNotFound();
        $this->withToken($token)->postJson('/api/admin/service-orders/999999/finalize')->assertNotFound();
        $this->withToken($token)->postJson('/api/admin/service-orders/999999/deliver')->assertNotFound();
    }

    private function createServiceOrder(string $status, string $plate = 'ABC1D23'): int
    {
        $document = $plate === 'ABC1D23' ? '52998224725' : $this->validCnpj($plate);
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente '.$plate,
            'document' => $document,
            'document_type' => $plate === 'ABC1D23' ? 'cpf' : 'cnpj',
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
        $serviceId = DB::table('services')->insertGetId([
            'name' => 'Servico '.$plate,
            'unit_price' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inventoryItemId = DB::table('inventory_items')->insertGetId([
            'name' => 'Peca '.$plate,
            'type' => 'part',
            'unit_price' => 5000,
            'quantity_available' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $serviceOrderId = DB::table('service_orders')->insertGetId([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'status' => $status,
            'total_amount' => 25000,
            'received_at' => now()->subHours(3),
            'execution_started_at' => $status === 'in_execution' ? now()->subHour() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('service_order_services')->insert([
            'service_order_id' => $serviceOrderId,
            'service_id' => $serviceId,
            'quantity' => 2,
            'unit_price_snapshot' => 10000,
        ]);
        DB::table('service_order_inventory_items')->insert([
            'service_order_id' => $serviceOrderId,
            'inventory_item_id' => $inventoryItemId,
            'type_snapshot' => 'part',
            'quantity' => 1,
            'unit_price_snapshot' => 5000,
        ]);

        return $serviceOrderId;
    }

    private function validCnpj(string $seed): string
    {
        $base = str_pad((string) (abs(crc32($seed)) % 1000000000000), 12, '0', STR_PAD_LEFT);
        $firstDigit = $this->cnpjDigit($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $secondDigit = $this->cnpjDigit($base.$firstDigit, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $base.$firstDigit.$secondDigit;
    }

    private function cnpjDigit(string $value, array $weights): int
    {
        $sum = 0;
        foreach (str_split($value) as $index => $digit) {
            $sum += (int) $digit * $weights[$index];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
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
