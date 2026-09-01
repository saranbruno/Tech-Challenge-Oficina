<?php

namespace App\Domain\ServiceOrder;

final readonly class ServiceOrderExecutionTimeMetrics
{
    public function __construct(
        public int $eligibleOrders,
        public ?int $averageTotalSeconds,
        public array $averageSecondsByStatus,
    ) {}
}
