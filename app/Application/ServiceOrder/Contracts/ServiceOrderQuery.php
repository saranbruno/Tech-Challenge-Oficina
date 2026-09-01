<?php

namespace App\Application\ServiceOrder\Contracts;

use App\Application\Shared\Data\PaginatedResult;
use App\Domain\ServiceOrder\ServiceOrder;

interface ServiceOrderQuery
{
    public function paginate(int $perPage): PaginatedResult;

    public function findForClientOrFail(string $customerDocument, string $trackingTokenHash): ServiceOrder;
}
