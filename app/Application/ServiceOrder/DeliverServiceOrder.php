<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class DeliverServiceOrder
{
    public function __construct(
        private ServiceOrderRepository $serviceOrders,
        private NotifyServiceOrderStatus $statusNotifications,
    ) {}

    public function execute(int $serviceOrderId, DateTimeImmutable $occurredAt): ServiceOrder
    {
        $serviceOrder = $this->serviceOrders->findOrFail($serviceOrderId);
        $serviceOrder->deliver($occurredAt);

        $updatedServiceOrder = $this->serviceOrders->update($serviceOrder);
        $this->statusNotifications->execute($updatedServiceOrder);

        return $updatedServiceOrder;
    }
}
