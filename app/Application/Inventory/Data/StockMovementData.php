<?php

namespace App\Application\Inventory\Data;

final readonly class StockMovementData
{
    public function __construct(
        public int $id,
        public int $inventoryItemId,
        public ?int $adminUserId,
        public ?int $serviceOrderId,
        public string $type,
        public int $quantityChange,
        public int $quantityBefore,
        public int $quantityAfter,
        public string $createdAt,
    ) {}
}
