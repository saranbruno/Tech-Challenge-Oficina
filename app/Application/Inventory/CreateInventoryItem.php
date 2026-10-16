<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Domain\Inventory\InventoryItem;

final readonly class CreateInventoryItem
{
    public function __construct(
        private InventoryItemRepository $items,
        private InventoryItemDataFactory $factory,
    ) {}

    public function execute(string $name, string $type, int $unitPrice, int $quantityAvailable, int $adminId): InventoryItem
    {
        return $this->items->create(
            $this->factory->make(null, $name, $type, $unitPrice, $quantityAvailable),
            $adminId,
        );
    }
}
