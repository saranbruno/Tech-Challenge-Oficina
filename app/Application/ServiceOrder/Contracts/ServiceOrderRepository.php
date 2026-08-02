<?php

namespace App\Application\ServiceOrder\Contracts;

use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

interface ServiceOrderRepository
{
    public function completedForMetrics(
        ?DateTimeImmutable $deliveredFrom,
        ?DateTimeImmutable $deliveredTo,
        ?int $serviceId,
    ): array;

    public function paginate(int $perPage): mixed;

    public function create(ServiceOrder $serviceOrder): ServiceOrder;

    public function findOrFail(int $id): ServiceOrder;

    public function findForClientOrFail(string $customerDocument, string $trackingTokenHash): ServiceOrder;

    public function update(ServiceOrder $serviceOrder): ServiceOrder;

    public function approveForClient(
        string $customerDocument,
        string $trackingTokenHash,
        DateTimeImmutable $occurredAt,
    ): ServiceOrder;
}
