<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Domain\Inventory\InventoryItem;

final readonly class AdjustInventoryStock
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $id, int $quantity, int $adminId): InventoryItem
    {
        return $this->items->adjustStock($id, $quantity, $adminId);
    }
}
