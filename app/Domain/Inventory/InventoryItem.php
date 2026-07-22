<?php

namespace App\Domain\Inventory;

use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Inventory\ValueObjects\StockQuantity;
use App\Domain\Service\ValueObjects\UnitPrice;
use DomainException;

final readonly class InventoryItem
{
    public string $name;

    public function __construct(
        public ?int $id,
        string $name,
        public InventoryItemType $type,
        public UnitPrice $unitPrice,
        public StockQuantity $quantityAvailable,
    ) {
        $this->name = trim($name);

        if ($this->name === '') {
            throw new DomainException('O nome do item e obrigatorio.');
        }
    }
}
