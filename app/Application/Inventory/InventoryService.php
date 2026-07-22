<?php

namespace App\Application\Inventory;

use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Inventory\InventoryItem;
use App\Domain\Inventory\ValueObjects\StockQuantity;
use App\Domain\Service\ValueObjects\UnitPrice;

final readonly class InventoryService
{
    public function __construct(private InventoryItemRepository $items) {}

    public function list(int $perPage): mixed
    {
        return $this->items->paginate($perPage);
    }

    public function find(int $id): InventoryItem
    {
        return $this->items->findOrFail($id);
    }

    public function create(string $name, string $type, int $unitPrice, int $quantityAvailable, int $adminId): InventoryItem
    {
        return $this->items->create($this->make(null, $name, $type, $unitPrice, $quantityAvailable), $adminId);
    }

    public function update(int $id, string $name, string $type, int $unitPrice): InventoryItem
    {
        $current = $this->items->findOrFail($id);

        return $this->items->update($this->make(
            $id,
            $name,
            $type,
            $unitPrice,
            $current->quantityAvailable->value,
        ));
    }

    public function adjustStock(int $id, int $quantity, int $adminId): InventoryItem
    {
        return $this->items->adjustStock($id, $quantity, $adminId);
    }

    public function movements(int $id, int $perPage): mixed
    {
        return $this->items->movements($id, $perPage);
    }

    public function delete(int $id): void
    {
        $this->items->delete($id);
    }

    private function make(?int $id, string $name, string $type, int $unitPrice, int $quantity): InventoryItem
    {
        return new InventoryItem(
            $id,
            $name,
            InventoryItemType::from($type),
            new UnitPrice($unitPrice),
            new StockQuantity($quantity),
        );
    }
}
