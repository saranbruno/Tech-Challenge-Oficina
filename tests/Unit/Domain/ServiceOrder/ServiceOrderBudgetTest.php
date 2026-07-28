<?php

namespace Tests\Unit\Domain\ServiceOrder;

use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Service\ValueObjects\UnitPrice;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderInventoryItem;
use App\Domain\ServiceOrder\ServiceOrderService;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class ServiceOrderBudgetTest extends TestCase
{
    public function test_calculates_item_subtotals_and_total_in_integer_cents(): void
    {
        $order = ServiceOrder::receive(1, 1, new DateTimeImmutable);
        $service = new ServiceOrderService(1, 2, new UnitPrice(15000));
        $part = new ServiceOrderInventoryItem(1, InventoryItemType::Part, 3, new UnitPrice(2500));
        $supply = new ServiceOrderInventoryItem(2, InventoryItemType::Supply, 4, new UnitPrice(750));

        $order->addService($service);
        $order->addInventoryItem($part);
        $order->addInventoryItem($supply);

        self::assertSame(30000, $service->subtotal());
        self::assertSame(7500, $part->subtotal());
        self::assertSame(40500, $order->totalAmount());
    }

    public function test_duplicate_inventory_item_is_rejected(): void
    {
        $order = ServiceOrder::receive(1, 1, new DateTimeImmutable);
        $order->addInventoryItem(new ServiceOrderInventoryItem(
            1,
            InventoryItemType::Part,
            1,
            new UnitPrice(1000),
        ));

        $this->expectException(DomainException::class);

        $order->addInventoryItem(new ServiceOrderInventoryItem(
            1,
            InventoryItemType::Part,
            2,
            new UnitPrice(1000),
        ));
    }

    public function test_non_positive_inventory_quantity_is_rejected(): void
    {
        $this->expectException(DomainException::class);

        new ServiceOrderInventoryItem(1, InventoryItemType::Supply, 0, new UnitPrice(1000));
    }
}
