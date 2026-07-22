<?php

namespace App\Application\Inventory\Contracts;

use App\Domain\Inventory\InventoryItem;

interface InventoryItemRepository
{
    public function paginate(int $perPage): mixed;

    public function findOrFail(int $id): InventoryItem;

    public function create(InventoryItem $item, int $adminId): InventoryItem;

    public function update(InventoryItem $item): InventoryItem;

    public function adjustStock(int $id, int $quantity, int $adminId): InventoryItem;

    public function movements(int $id, int $perPage): mixed;

    public function delete(int $id): void;
}
