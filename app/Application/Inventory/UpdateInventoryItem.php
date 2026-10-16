<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Domain\Inventory\InventoryItem;

final readonly class UpdateInventoryItem
{
    public function __construct(
        private InventoryItemRepository $items,
        private InventoryItemDataFactory $factory,
    ) {}

    public function execute(int $id, string $name, string $type, int $unitPrice): InventoryItem
    {
        $current = $this->items->findOrFail($id);

        return $this->items->update($this->factory->make(
            $id,
            $name,
            $type,
            $unitPrice,
            $current->quantityAvailable->value,
        ));
    }
}
