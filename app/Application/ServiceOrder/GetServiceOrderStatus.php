<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderStatusQuery;
use App\Application\ServiceOrder\Data\ServiceOrderStatusData;

final readonly class GetServiceOrderStatus
{
    public function __construct(private ServiceOrderStatusQuery $statuses) {}

    public function execute(int $serviceOrderId): ServiceOrderStatusData
    {
        return $this->statuses->findOrFail($serviceOrderId);
    }
}
