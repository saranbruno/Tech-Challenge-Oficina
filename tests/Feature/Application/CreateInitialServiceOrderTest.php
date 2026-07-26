<?php

namespace Tests\Feature\Application;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\ServiceOrder\CreateInitialServiceOrder;
use App\Application\ServiceOrder\Data\RequestedServiceData;
use App\Application\ServiceOrder\Exceptions\VehicleDoesNotBelongToCustomer;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use DateTimeImmutable;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateInitialServiceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_identifies_customer_validates_vehicle_and_persists_service_snapshots(): void
    {
        [$customerId, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        $firstServiceId = $this->createService('Troca de oleo', 15000);
        $secondServiceId = $this->createService('Alinhamento', 9000);

        $order = app(CreateInitialServiceOrder::class)->execute(
            '529.982.247-25',
            $vehicleId,
            [
                new RequestedServiceData($firstServiceId, 1),
                new RequestedServiceData($secondServiceId, 2),
            ],
            new DateTimeImmutable('2026-07-22 10:00:00'),
        );

        self::assertSame($customerId, $order->customerId);
        self::assertSame($vehicleId, $order->vehicleId);
        self::assertSame(ServiceOrderStatus::Received, $order->status);
        self::assertCount(2, $order->services());
        self::assertSame(15000, $order->services()[0]->unitPriceSnapshot->cents);
        self::assertSame(2, $order->services()[1]->quantity);
        $this->assertDatabaseHas('service_order_services', [
            'service_order_id' => $order->id,
            'service_id' => $secondServiceId,
            'quantity' => 2,
            'unit_price_snapshot' => 9000,
        ]);
    }

    public function test_catalogue_price_change_does_not_change_persisted_snapshot(): void
    {
        [, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        $serviceId = $this->createService('Troca de oleo', 15000);
        $useCase = app(CreateInitialServiceOrder::class);
        $order = $useCase->execute(
            '52998224725',
            $vehicleId,
            [new RequestedServiceData($serviceId, 1)],
            new DateTimeImmutable('2026-07-22 10:00:00'),
        );

        DB::table('services')->where('id', $serviceId)->update(['unit_price' => 20000]);
        $reloaded = app(ServiceOrderRepository::class)->findOrFail($order->id);

        self::assertSame(15000, $reloaded->services()[0]->unitPriceSnapshot->cents);
    }

    public function test_case_identifies_customer_by_formatted_cnpj(): void
    {
        [$customerId, $vehicleId] = $this->createCustomerAndVehicle('04252011000110', 'BRA2E19');
        $serviceId = $this->createService('Alinhamento', 9000);

        $order = app(CreateInitialServiceOrder::class)->execute(
            '04.252.011/0001-10',
            $vehicleId,
            [new RequestedServiceData($serviceId, 1)],
            new DateTimeImmutable('2026-07-22 10:00:00'),
        );

        self::assertSame($customerId, $order->customerId);
    }

    public function test_vehicle_from_another_customer_is_rejected_without_partial_order(): void
    {
        $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        [, $otherVehicleId] = $this->createCustomerAndVehicle('04252011000110', 'BRA2E19');
        $serviceId = $this->createService('Alinhamento', 9000);

        $this->expectException(VehicleDoesNotBelongToCustomer::class);

        try {
            app(CreateInitialServiceOrder::class)->execute(
                '52998224725',
                $otherVehicleId,
                [new RequestedServiceData($serviceId, 1)],
                new DateTimeImmutable('2026-07-22 10:00:00'),
            );
        } finally {
            self::assertSame(0, DB::table('service_orders')->count());
        }
    }

    public function test_order_without_services_is_rejected(): void
    {
        [, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');

        $this->expectException(DomainException::class);

        app(CreateInitialServiceOrder::class)->execute(
            '52998224725',
            $vehicleId,
            [],
            new DateTimeImmutable('2026-07-22 10:00:00'),
        );
    }

    public function test_postgresql_rejects_non_positive_service_quantity(): void
    {
        [, $vehicleId] = $this->createCustomerAndVehicle('52998224725', 'ABC1D23');
        $serviceId = $this->createService('Alinhamento', 9000);
        $order = app(CreateInitialServiceOrder::class)->execute(
            '52998224725',
            $vehicleId,
            [new RequestedServiceData($serviceId, 1)],
            new DateTimeImmutable('2026-07-22 10:00:00'),
        );

        $this->expectException(QueryException::class);

        DB::table('service_order_services')->where('service_order_id', $order->id)->update(['quantity' => 0]);
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
}
