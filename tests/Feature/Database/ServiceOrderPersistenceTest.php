<?php

namespace Tests\Feature\Database;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceOrderPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_repository_persists_and_reconstitutes_the_order_lifecycle(): void
    {
        [$customerId, $vehicleId] = $this->createCustomerAndVehicle();
        $repository = app(ServiceOrderRepository::class);
        $order = ServiceOrder::receive($customerId, $vehicleId, new DateTimeImmutable('2026-07-22 09:00:00'));

        $persisted = $repository->create($order);
        $persisted->startDiagnosis(new DateTimeImmutable('2026-07-22 09:30:00'));
        $updated = $repository->update($persisted);
        $found = $repository->findOrFail($updated->id);

        self::assertSame(ServiceOrderStatus::InDiagnosis, $found->status);
        self::assertSame($customerId, $found->customerId);
        self::assertSame($vehicleId, $found->vehicleId);
        self::assertSame('2026-07-22 09:30:00', $found->diagnosisStartedAt?->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('service_orders', [
            'id' => $found->id,
            'status' => 'in_diagnosis',
        ]);
    }

    public function test_postgresql_rejects_an_unknown_status(): void
    {
        [$customerId, $vehicleId] = $this->createCustomerAndVehicle();

        $this->expectException(QueryException::class);

        $this->insertOrder($customerId, $vehicleId, 'unknown');
    }

    public function test_postgresql_requires_existing_customer_and_vehicle(): void
    {
        $this->expectException(QueryException::class);

        $this->insertOrder(999999, 999999, 'received');
    }

    private function createCustomerAndVehicle(): array
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

        return [$customerId, $vehicleId];
    }

    private function insertOrder(int $customerId, int $vehicleId, string $status): void
    {
        DB::table('service_orders')->insert([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'status' => $status,
            'received_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
