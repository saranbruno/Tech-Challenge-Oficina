<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\ServiceOrder;

final readonly class GetServiceOrder
{
    public function __construct(private ServiceOrderRepository $serviceOrders) {}

    public function execute(int $serviceOrderId): ServiceOrder
    {
        return $this->serviceOrders->findOrFail($serviceOrderId);
    }
}
