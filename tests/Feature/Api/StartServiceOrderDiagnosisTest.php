<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StartServiceOrderDiagnosisTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_diagnosis_requires_administrative_authentication(): void
    {
        $this->postJson('/api/admin/service-orders/1/diagnosis/start')->assertUnauthorized();
    }

    public function test_admin_starts_diagnosis_and_persists_its_instant(): void
    {
        $serviceOrderId = $this->createServiceOrder();

        $response = $this->withToken($this->adminToken())
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/start")
            ->assertOk()
            ->assertJsonPath('data.id', $serviceOrderId)
            ->assertJsonPath('data.status', 'in_diagnosis');

        self::assertNotNull($response->json('data.diagnosis_started_at'));
        $this->assertDatabaseHas('service_orders', [
            'id' => $serviceOrderId,
            'status' => 'in_diagnosis',
        ]);
        self::assertNotNull(DB::table('service_orders')->where('id', $serviceOrderId)->value('diagnosis_started_at'));
    }

    public function test_repeated_start_is_rejected_without_changing_the_first_instant(): void
    {
        $serviceOrderId = $this->createServiceOrder();
        $token = $this->adminToken();

        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/start")
            ->assertOk();
        $firstInstant = DB::table('service_orders')->where('id', $serviceOrderId)->value('diagnosis_started_at');

        $this->withToken($token)
            ->postJson("/api/admin/service-orders/{$serviceOrderId}/diagnosis/start")
            ->assertConflict()
            ->assertJsonPath('error.code', 'invalid_service_order_transition');

        self::assertEquals(
            $firstInstant,
            DB::table('service_orders')->where('id', $serviceOrderId)->value('diagnosis_started_at'),
        );
    }

    public function test_unknown_service_order_returns_not_found(): void
    {
        $this->withToken($this->adminToken())
            ->postJson('/api/admin/service-orders/999999/diagnosis/start')
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

    private function createServiceOrder(): int
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

        return DB::table('service_orders')->insertGetId([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'status' => 'received',
            'total_amount' => 0,
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
