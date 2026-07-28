<?php

namespace App\Domain\ServiceOrder;

use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Service\ValueObjects\UnitPrice;
use DomainException;

final readonly class ServiceOrderInventoryItem
{
    public function __construct(
        public int $inventoryItemId,
        public InventoryItemType $typeSnapshot,
        public int $quantity,
        public UnitPrice $unitPriceSnapshot,
    ) {
        if ($inventoryItemId < 1) {
            throw new DomainException('O item de estoque associado deve ser valido.');
        }

        if ($quantity < 1) {
            throw new DomainException('A quantidade do item de estoque deve ser maior que zero.');
        }
    }

    public function subtotal(): int
    {
        return $this->quantity * $this->unitPriceSnapshot->cents;
    }
}
