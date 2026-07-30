<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class CompleteServiceOrderDiagnosis
{
    public function __construct(private ServiceOrderRepository $serviceOrders) {}

    public function execute(int $serviceOrderId, DateTimeImmutable $occurredAt): ServiceOrder
    {
        $serviceOrder = $this->serviceOrders->findOrFail($serviceOrderId);
        $serviceOrder->makeBudgetAvailable($occurredAt);

        return $this->serviceOrders->update($serviceOrder);
    }
}
