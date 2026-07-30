<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\Customer\ValueObjects\Document;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class ApproveClientServiceOrderBudget
{
    public function __construct(private ServiceOrderRepository $serviceOrders) {}

    public function execute(
        string $customerDocument,
        string $trackingToken,
        DateTimeImmutable $occurredAt,
    ): ServiceOrder {
        $serviceOrder = $this->serviceOrders->findForClientOrFail(
            (new Document($customerDocument))->value,
            hash('sha256', $trackingToken),
        );
        $serviceOrder->approveBudget($occurredAt);

        return $this->serviceOrders->update($serviceOrder);
    }
}
