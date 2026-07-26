<?php

namespace App\Domain\ServiceOrder;

use App\Domain\Service\ValueObjects\UnitPrice;
use DomainException;

final readonly class ServiceOrderService
{
    public function __construct(
        public int $serviceId,
        public int $quantity,
        public UnitPrice $unitPriceSnapshot,
    ) {
        if ($serviceId < 1) {
            throw new DomainException('O servico associado deve ser valido.');
        }

        if ($quantity < 1) {
            throw new DomainException('A quantidade do servico deve ser maior que zero.');
        }
    }
}
