<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class FinalizeServiceOrder
{
    public function __construct(
        private ServiceOrderRepository $serviceOrders,
        private NotifyServiceOrderStatus $statusNotifications,
    ) {}

    public function execute(int $serviceOrderId, DateTimeImmutable $occurredAt): ServiceOrder
    {
        $serviceOrder = $this->serviceOrders->findOrFail($serviceOrderId);
        $serviceOrder->finalize($occurredAt);

        $updatedServiceOrder = $this->serviceOrders->update($serviceOrder);
        $this->statusNotifications->execute($updatedServiceOrder);

        return $updatedServiceOrder;
    }
}
