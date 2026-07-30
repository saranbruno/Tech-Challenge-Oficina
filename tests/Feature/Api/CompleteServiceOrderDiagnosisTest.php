<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CompleteServiceOrderDiagnosisTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_diagnosis_requires_administrative_authentication(): void
    {
        $this->postJson('/api/admin/service-orders/1/diagnosis/complete')->assertUnauthorized();
    }

    public function test_admin_completes_diagnosis_and_makes_budget_available(): void
    {
        $serviceOrderId = $this->createServiceOrder(true);

        $response = $this->withToken($this->adminToken())
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/complete")
            ->assertOk()
            ->assertJsonPath('data.id', $serviceOrderId)
            ->assertJsonPath('data.status', 'awaiting_approval')
            ->assertJsonPath('data.total_amount', 15000);

        self::assertNotNull($response->json('data.awaiting_approval_at'));
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrderId,
            'status' => 'awaiting_approval',
            'total_amount' => 15000,
        ]);
        self::assertNotNull(DB::table('service_orders')->where('id', $serviceOrderId)->value('awaiting_approval_at'));
    }

    public function test_repeated_completion_is_rejected_without_changing_the_first_instant(): void
    {
        $serviceOrderId = $this->createServiceOrder(true);
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/complete")
            ->assertOk();
        $firstInstant = DB::table('service_orders')->where('id', $serviceOrderId)->value('awaiting_approval_at');

        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/complete")
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');

        self::assertEquals(
            $firstInstant,
            DB::table('service_orders')->where('id', $serviceOrderId)->value('awaiting_approval_at'),
        );
    }

    public function test_budget_without_services_is_not_made_available(): void
    {
        $serviceOrderId = $this->createServiceOrder(false);

        $this->withToken($this->adminToken())
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/complete")
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_budget');

        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrderId,
            'status' => 'in_diagnosis',
            'awaiting_approval_at' => null,
        ]);
    }

    public function test_unknown_service_order_returns_not_found(): void
    {
        $this->withToken($this->adminToken())
            ->postJson('/api/admin/service-orders/999999/diagnosis/complete')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
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

    private function createServiceOrder(bool $withService): int
    {
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente OS',
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
        $serviceOrderId = DB::table('service_orders')->insertGetId([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'status' => 'in_diagnosis',
            'total_amount' => $withService ? 15000 : 0,
            'received_at' => now()->subHour(),
            'diagnosis_started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($withService) {
            $serviceId = DB::table('services')->insertGetId([
                'name' => 'Diagnostico',
                'unit_price' => 15000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('service_order_services')->insert([
                'service_order_id' => $serviceOrderId,
                'service_id' => $serviceId,
                'quantity' => 1,
                'unit_price_snapshot' => 15000,
            ]);
        }

        return $serviceOrderId;
    }
}
