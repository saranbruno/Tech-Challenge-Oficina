<?php

namespace App\Application\ServiceOrder\Data;

final readonly class RequestedInventoryItemCollection
{
    private array $items;

    public function __construct(RequestedInventoryItemData ...$items)
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }
}
