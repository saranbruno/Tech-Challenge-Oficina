<?php

namespace Tests\Unit\Domain\ServiceOrder;

use App\Domain\Service\ValueObjects\UnitPrice;
use App\Domain\ServiceOrder\ServiceOrderService;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ServiceOrderServiceTest extends TestCase
{
    public function test_service_item_preserves_quantity_and_unit_price_snapshot(): void
    {
        $item = new ServiceOrderService(10, 3, new UnitPrice(12500));

        self::assertSame(10, $item->serviceId);
        self::assertSame(3, $item->quantity);
        self::assertSame(12500, $item->unitPriceSnapshot->cents);
    }

    #[DataProvider('invalidItemProvider')]
    public function test_invalid_service_item_is_rejected(int $serviceId, int $quantity): void
    {
        $this->expectException(DomainException::class);

        new ServiceOrderService($serviceId, $quantity, new UnitPrice(100));
    }

    public static function invalidItemProvider(): array
    {
        return [
            'servico invalido' => [0, 1],
            'quantidade zero' => [1, 0],
            'quantidade negativa' => [1, -1],
        ];
    }
}
