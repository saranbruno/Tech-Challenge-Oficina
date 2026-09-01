<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;

final readonly class ListInventoryItems
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $perPage): mixed
    {
        return $this->items->paginate($perPage);
    }
}
