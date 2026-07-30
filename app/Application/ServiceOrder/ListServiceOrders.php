<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;

final readonly class ListServiceOrders
{
    public function __construct(private ServiceOrderRepository $serviceOrders) {}

    public function execute(int $perPage): mixed
    {
        return $this->serviceOrders->paginate($perPage);
    }
}
