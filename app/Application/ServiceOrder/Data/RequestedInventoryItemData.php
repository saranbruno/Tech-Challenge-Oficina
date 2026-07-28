<?php

namespace App\Application\ServiceOrder\Data;

final readonly class RequestedInventoryItemData
{
    public function __construct(
        public int $inventoryItemId,
        public int $quantity,
    ) {}
}
