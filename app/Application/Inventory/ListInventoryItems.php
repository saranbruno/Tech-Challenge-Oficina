<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Application\Shared\Data\PaginatedResult;

final readonly class ListInventoryItems
{
    public function __construct(private InventoryItemRepository $items) {}

    public function execute(int $perPage): PaginatedResult
    {
        return $this->items->paginate($perPage);
    }
}
