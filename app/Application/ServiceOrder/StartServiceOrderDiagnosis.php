<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class StartServiceOrderDiagnosis
{
    public function __construct(private ServiceOrderRepository $serviceOrders) {}

    public function execute(int $serviceOrderId, DateTimeImmutable $occurredAt): ServiceOrder
    {
        $serviceOrder = $this->serviceOrders->findOrFail($serviceOrderId);
        $serviceOrder->startDiagnosis($occurredAt);

        return $this->serviceOrders->update($serviceOrder);
    }
}
