<?php

namespace App\Domain\ServiceOrder;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderBudget;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderTransition;
use DateTimeImmutable;

final class ServiceOrder
{
    private array $services = [];

    private array $inventoryItems = [];

    private function __construct(
        public readonly ?int $id,
        public readonly int $customerId,
        public readonly int $vehicleId,
        public private(set) ServiceOrderStatus $status,
        public readonly DateTimeImmutable $receivedAt,
        public private(set) ?DateTimeImmutable $diagnosisStartedAt = null,
        public private(set) ?DateTimeImmutable $awaitingApprovalAt = null,
        public private(set) ?DateTimeImmutable $executionStartedAt = null,
        public private(set) ?DateTimeImmutable $finalizedAt = null,
        public private(set) ?DateTimeImmutable $deliveredAt = null,
        public private(set) ?DateTimeImmutable $cancelledAt = null,
    ) {}

    public static function receive(int $customerId, int $vehicleId, DateTimeImmutable $receivedAt): self
    {
        return new self(null, $customerId, $vehicleId, ServiceOrderStatus::Received, $receivedAt);
    }

    public static function reconstitute(
        int $id,
        int $customerId,
        int $vehicleId,
        ServiceOrderStatus $status,
        DateTimeImmutable $receivedAt,
        ?DateTimeImmutable $diagnosisStartedAt,
        ?DateTimeImmutable $awaitingApprovalAt,
        ?DateTimeImmutable $executionStartedAt,
        ?DateTimeImmutable $finalizedAt,
        ?DateTimeImmutable $deliveredAt,
        ?DateTimeImmutable $cancelledAt,
        array $services = [],
        array $inventoryItems = [],
    ): self {
        $serviceOrder = new self(
            $id,
            $customerId,
            $vehicleId,
            $status,
            $receivedAt,
            $diagnosisStartedAt,
            $awaitingApprovalAt,
            $executionStartedAt,
            $finalizedAt,
            $deliveredAt,
            $cancelledAt,
        );

        foreach ($services as $service) {
            $serviceOrder->addService($service);
        }

        foreach ($inventoryItems as $inventoryItem) {
            $serviceOrder->addInventoryItem($inventoryItem);
        }

        return $serviceOrder;
    }

    public function addService(ServiceOrderService $service): void
    {
        if (isset($this->services[$service->serviceId])) {
            throw new \DomainException('O servico nao pode ser associado mais de uma vez a ordem de servico.');
        }

        $this->services[$service->serviceId] = $service;
    }

    public function services(): array
    {
        return array_values($this->services);
    }

    public function addInventoryItem(ServiceOrderInventoryItem $inventoryItem): void
    {
        if (isset($this->inventoryItems[$inventoryItem->inventoryItemId])) {
            throw new \DomainException('O item de estoque nao pode ser associado mais de uma vez a ordem de servico.');
        }

        $this->inventoryItems[$inventoryItem->inventoryItemId] = $inventoryItem;
    }

    public function inventoryItems(): array
    {
        return array_values($this->inventoryItems);
    }

    public function totalAmount(): int
    {
        return array_sum(array_map(
            fn (ServiceOrderService|ServiceOrderInventoryItem $item): int => $item->subtotal(),
            [...$this->services(), ...$this->inventoryItems()],
        ));
    }

    public function startDiagnosis(DateTimeImmutable $occurredAt): void
    {
        $this->transition(ServiceOrderStatus::Received, ServiceOrderStatus::InDiagnosis);
        $this->diagnosisStartedAt = $occurredAt;
    }

    public function makeBudgetAvailable(DateTimeImmutable $occurredAt): void
    {
        if ($this->status !== ServiceOrderStatus::InDiagnosis) {
            throw new InvalidServiceOrderTransition("Transicao invalida de {$this->status->value} para ".ServiceOrderStatus::AwaitingApproval->value.'.');
        }

        if ($this->services === []) {
            throw new InvalidServiceOrderBudget('A ordem de servico precisa possuir ao menos um servico para disponibilizar o orcamento.');
        }

        $this->transition(ServiceOrderStatus::InDiagnosis, ServiceOrderStatus::AwaitingApproval);
        $this->awaitingApprovalAt = $occurredAt;
    }

    public function approveBudget(DateTimeImmutable $occurredAt): void
    {
        $this->transition(ServiceOrderStatus::AwaitingApproval, ServiceOrderStatus::InExecution);
        $this->executionStartedAt = $occurredAt;
    }

    public function finalize(DateTimeImmutable $occurredAt): void
    {
        $this->transition(ServiceOrderStatus::InExecution, ServiceOrderStatus::Finalized);
        $this->finalizedAt = $occurredAt;
    }

    public function deliver(DateTimeImmutable $occurredAt): void
    {
        $this->transition(ServiceOrderStatus::Finalized, ServiceOrderStatus::Delivered);
        $this->deliveredAt = $occurredAt;
    }

    public function cancel(DateTimeImmutable $occurredAt): void
    {
        if (! in_array($this->status, [
            ServiceOrderStatus::Received,
            ServiceOrderStatus::InDiagnosis,
            ServiceOrderStatus::AwaitingApproval,
        ], true)) {
            throw new InvalidServiceOrderTransition("A ordem de servico nao pode ser cancelada a partir de {$this->status->value}.");
        }

        $this->status = ServiceOrderStatus::Cancelled;
        $this->cancelledAt = $occurredAt;
    }

    private function transition(ServiceOrderStatus $expected, ServiceOrderStatus $next): void
    {
        if ($this->status !== $expected) {
            throw new InvalidServiceOrderTransition("Transicao invalida de {$this->status->value} para {$next->value}.");
        }

        $this->status = $next;
    }
}
