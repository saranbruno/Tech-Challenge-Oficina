<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderApproval;
use App\Domain\Customer\ValueObjects\Document;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class ApproveClientServiceOrderBudget
{
    public function __construct(private ServiceOrderApproval $serviceOrders) {}

    public function execute(
        string $customerDocument,
        string $trackingToken,
        DateTimeImmutable $occurredAt,
    ): ServiceOrder {
        return $this->serviceOrders->approveForClient(
            (new Document($customerDocument))->value,
            hash('sha256', $trackingToken),
            $occurredAt,
        );
    }
}
