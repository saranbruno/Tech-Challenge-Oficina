<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Domain\Inventory\InventoryItem;

final readonly class GetInventoryItem
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $id): InventoryItem
    {
        return $this->items->findOrFail($id);
    }
}
