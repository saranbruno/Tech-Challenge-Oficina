<?php

namespace App\Domain\Inventory\ValueObjects;

use DomainException;

final readonly class StockQuantity
{
    public function __construct(public int $value)
    {
        if ($value < 0) {
            throw new DomainException('A quantidade em estoque nao pode ser negativa.');
        }
    }
}
