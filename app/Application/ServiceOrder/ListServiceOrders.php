<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderQuery;
use App\Application\Shared\Data\PaginatedResult;

final readonly class ListServiceOrders
{
    public function __construct(private ServiceOrderQuery $serviceOrders) {}

    public function execute(int $perPage): PaginatedResult
    {
        return $this->serviceOrders->paginate($perPage);
    }
}
