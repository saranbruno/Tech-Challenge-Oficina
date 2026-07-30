<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\Customer\ValueObjects\Document;
use App\Domain\ServiceOrder\ServiceOrder;

final readonly class TrackServiceOrder
{
    public function __construct(private ServiceOrderRepository $serviceOrders) {}

    public function execute(string $customerDocument, string $trackingToken): ServiceOrder
    {
        return $this->serviceOrders->findForClientOrFail(
            (new Document($customerDocument))->value,
            hash('sha256', $trackingToken),
        );
    }
}
