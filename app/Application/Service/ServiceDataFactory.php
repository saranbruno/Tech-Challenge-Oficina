<?php

namespace App\Application\Service;

use App\Domain\Service\Service;
use App\Domain\Service\ValueObjects\UnitPrice;

final readonly class ServiceDataFactory
{
    public function make(?int $id, string $name, int $unitPrice): Service
    {
        return new Service($id, $name, new UnitPrice($unitPrice));
    }
}
