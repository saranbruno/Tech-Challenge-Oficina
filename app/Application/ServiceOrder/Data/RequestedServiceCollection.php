<?php

namespace App\Application\ServiceOrder\Data;

final readonly class RequestedServiceCollection
{
    private array $items;

    public function __construct(RequestedServiceData ...$items)
    {
        $this->items = $items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function all(): array
    {
        return $this->items;
    }
}
