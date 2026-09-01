<?php

namespace App\Application\ServiceOrder\Contracts;

use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

interface ServiceOrderApproval
{
    public function approveForClient(
        string $customerDocument,
        string $trackingTokenHash,
        DateTimeImmutable $occurredAt,
    ): ServiceOrder;
}
