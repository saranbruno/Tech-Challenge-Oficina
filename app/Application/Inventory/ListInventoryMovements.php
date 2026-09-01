<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;

final readonly class ListInventoryMovements
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $id, int $perPage): mixed
    {
        return $this->items->movements($id, $perPage);
    }
}
