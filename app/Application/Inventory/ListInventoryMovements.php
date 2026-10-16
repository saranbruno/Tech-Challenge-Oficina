<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Application\Shared\Data\PaginatedResult;

final readonly class ListInventoryMovements
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $id, int $perPage): PaginatedResult
    {
        return $this->items->movements($id, $perPage);
    }
}
