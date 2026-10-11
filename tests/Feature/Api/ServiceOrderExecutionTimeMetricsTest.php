<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceOrderExecutionTimeMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_require_administrative_authentication(): void
    {
        $this->getJson('/api/admin/service-orders-metrics/execution-time')->assertUnauthorized();
    }

    public function test_metrics_include_only_delivered_orders_and_calculate_all_intervals(): void
    {
        $serviceId = $this->createService('Troca de oleo');
        $this->createOrder($serviceId, 'delivered', '2026-07-01 08:00:00', [10, 30, 60, 100, 150]);
        $this->createOrder($serviceId, 'delivered', '2026-07-02 08:00:00', [20, 60, 120, 200, 300]);
        $this->createOrder($serviceId, 'finalized', '2026-07-03 08:00:00', [30, 90, 180, 300, null]);

        $this->withToken($this->adminToken())
            ->getJson('/api/admin/service-orders-metrics/execution-time')
            ->assertOk()
            ->assertJsonPath('data.eligible_orders', 2)
            ->assertJsonPath('data.average_total_seconds', 225)
            ->assertJsonPath('data.average_seconds_by_status.received', 15)
            ->assertJsonPath('data.average_seconds_by_status.in_diagnosis', 30)
            ->assertJsonPath('data.average_seconds_by_status.awaiting_approval', 45)
            ->assertJsonPath('data.average_seconds_by_status.in_execution', 60)
            ->assertJsonPath('data.average_seconds_by_status.finalized', 75);
    }

    public function test_metrics_filter_by_delivery_period_and_service(): void
    {
        $selectedServiceId = $this->createService('Alinhamento');
        $otherServiceId = $this->createService('Balanceamento');
        $this->createOrder($selectedServiceId, 'delivered', '2026-07-10 08:00:00', [60, 120, 180, 240, 300]);
        $this->createOrder($selectedServiceId, 'delivered', '2026-07-20 08:00:00', [120, 240, 360, 480, 600]);
        $this->createOrder($otherServiceId, 'delivered', '2026-07-10 08:00:00', [180, 360, 540, 720, 900]);

        $this->withToken($this->adminToken())
            ->getJson("/api/admin/service-orders-metrics/execution-time?delivered_from=2026-07-10&delivered_to=2026-07-10&service_id={$selectedServiceId}")
            ->assertOk()
            ->assertJsonPath('data.eligible_orders', 1)
            ->assertJsonPath('data.average_total_seconds', 300);
    }

    public function test_metrics_validate_filters_and_return_null_without_eligible_orders(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->getJson('/api/admin/service-orders-metrics/execution-time?delivered_from=2026-08-02&delivered_to=2026-08-01')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonStructure(['error' => ['details' => ['delivered_to']]]);

        $this->withToken($token)
            ->getJson('/api/admin/service-orders-metrics/execution-time')
            ->assertOk()
            ->assertJsonPath('data.eligible_orders', 0)
            ->assertJsonPath('data.average_total_seconds', null);
    }

    private function createService(string $name): int
    {
        return DB::table('services')->insertGetId([
            'name' => $name,
            'unit_price' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createOrder(int $serviceId, string $status, string $receivedAt, array $offsets): void
    {
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente '.uniqid(),
            'document' => str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT),
            'document_type' => 'cpf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $vehicleId = DB::table('vehicles')->insertGetId([
            'customer_id' => $customerId,
            'license_plate' => 'AAA'.random_int(1000, 9999),
            'brand' => 'Marca',
            'model' => 'Modelo',
            'year' => 2020,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $received = new \DateTimeImmutable($receivedAt);
        $at = fn (?int $minutes): ?\DateTimeImmutable => $minutes === null ? null : $received->modify("+{$minutes} seconds");
        $serviceOrderId = DB::table('service_orders')->insertGetId([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'status' => $status,
            'total_amount' => 10000,
            'received_at' => $received,
            'diagnosis_started_at' => $at($offsets[0]),
            'awaiting_approval_at' => $at($offsets[1]),
            'execution_started_at' => $at($offsets[2]),
            'finalized_at' => $at($offsets[3]),
            'delivered_at' => $at($offsets[4]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('service_order_services')->insert([
            'service_order_id' => $serviceOrderId,
            'service_id' => $serviceId,
            'quantity' => 1,
            'unit_price_snapshot' => 10000,
        ]);
    }

    private function adminToken(): string
    {
        User::factory()->create([
            'email' => 'metrics@example.com',
            'password' => 'secure-password',
        ]);

        return $this->postJson('/api/admin/auth/login', [
            'email' => 'metrics@example.com',
            'password' => 'secure-password',
        ])->json('access_token');
    }
}
