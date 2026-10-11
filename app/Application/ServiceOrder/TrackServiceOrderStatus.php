<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderStatusQuery;
use App\Application\ServiceOrder\Data\ServiceOrderStatusData;
use App\Domain\Customer\ValueObjects\Document;

final readonly class TrackServiceOrderStatus
{
    public function __construct(private ServiceOrderStatusQuery $statuses) {}

    public function execute(string $customerDocument, string $trackingToken): ServiceOrderStatusData
    {
        return $this->statuses->findForClientOrFail(
            (new Document($customerDocument))->value,
            hash('sha256', $trackingToken),
        );
    }
}
