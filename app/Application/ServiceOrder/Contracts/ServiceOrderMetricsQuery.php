<?php

namespace App\Application\ServiceOrder\Contracts;

use DateTimeImmutable;

interface ServiceOrderMetricsQuery
{
    public function completedForMetrics(
        ?DateTimeImmutable $deliveredFrom,
        ?DateTimeImmutable $deliveredTo,
        ?int $serviceId,
    ): array;
}
