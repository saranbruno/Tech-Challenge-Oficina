<?php

namespace App\Application\ServiceOrder\Contracts;

use App\Application\ServiceOrder\Data\ServiceOrderStatusData;

interface ServiceOrderStatusQuery
{
    public function findOrFail(int $serviceOrderId): ServiceOrderStatusData;

    public function findForClientOrFail(string $customerDocument, string $trackingTokenHash): ServiceOrderStatusData;
}
