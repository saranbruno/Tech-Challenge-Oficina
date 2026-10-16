<?php

namespace App\Application\ServiceOrder\Data;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use DateTimeImmutable;

final readonly class ServiceOrderStatusData
{
    public function __construct(
        public int $serviceOrderId,
        public ServiceOrderStatus $status,
        public DateTimeImmutable $lastTransitionAt,
    ) {}
}
