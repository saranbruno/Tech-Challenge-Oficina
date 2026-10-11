<?php

namespace App\Application\ServiceOrder\Contracts;

use App\Domain\ServiceOrder\ServiceOrder;

interface ServiceOrderRepository
{
    public function create(ServiceOrder $serviceOrder): ServiceOrder;

    public function findOrFail(int $id): ServiceOrder;

    public function update(ServiceOrder $serviceOrder): ServiceOrder;
}
