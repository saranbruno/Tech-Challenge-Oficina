<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\ServiceOrderExecutionTimeCalculator;
use DateTimeImmutable;

final readonly class GetServiceOrderExecutionTimeMetrics
{
    public function __construct(
        private ServiceOrderRepository $serviceOrders,
        private ServiceOrderExecutionTimeCalculator $calculator,
    ) {}

    public function execute(
        ?DateTimeImmutable $deliveredFrom,
        ?DateTimeImmutable $deliveredTo,
        ?int $serviceId,
    ): array {
        return $this->calculator->calculate($this->serviceOrders->completedForMetrics(
            $deliveredFrom,
            $deliveredTo,
            $serviceId,
        ));
    }
}
