<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;

final readonly class DeleteInventoryItem
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $id): void
    {
        $this->items->delete($id);
    }
}
