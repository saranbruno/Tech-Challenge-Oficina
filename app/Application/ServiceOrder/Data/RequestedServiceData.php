<?php

namespace App\Application\ServiceOrder\Data;

final readonly class RequestedServiceData
{
    public function __construct(
        public int $serviceId,
        public int $quantity,
    ) {}
}
