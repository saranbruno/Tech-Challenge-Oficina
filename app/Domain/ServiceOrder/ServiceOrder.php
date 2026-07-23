<?php

namespace App\Domain\ServiceOrder;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderTransition;
use DateTimeImmutable;

final class ServiceOrder
{
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
    ): self {
        return new self(
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
    }

    public function startDiagnosis(DateTimeImmutable $occurredAt): void
    {
        $this->transition(ServiceOrderStatus::Received, ServiceOrderStatus::InDiagnosis);
        $this->diagnosisStartedAt = $occurredAt;
    }

    public function makeBudgetAvailable(DateTimeImmutable $occurredAt): void
    {
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
