<?php

namespace App\Domain\Service\ValueObjects;

use DomainException;

final readonly class UnitPrice
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new DomainException('O valor unitario nao pode ser negativo.');
        }
    }
}
