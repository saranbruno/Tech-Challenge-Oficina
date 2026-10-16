<?php

namespace App\Application\Inventory;

use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Inventory\InventoryItem;
use App\Domain\Inventory\ValueObjects\StockQuantity;
use App\Domain\Service\ValueObjects\UnitPrice;

final readonly class InventoryItemDataFactory
{
    public function make(?int $id, string $name, string $type, int $unitPrice, int $quantity): InventoryItem
    {
        return new InventoryItem(
            $id,
            $name,
            InventoryItemType::from($type),
            new UnitPrice($unitPrice),
            new StockQuantity($quantity),
        );
    }
}
