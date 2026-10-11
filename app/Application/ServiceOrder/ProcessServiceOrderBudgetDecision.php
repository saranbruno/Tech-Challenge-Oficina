<?php

namespace App\Application\ServiceOrder;

use App\Application\ServiceOrder\Contracts\ServiceOrderBudgetDecision;
use App\Application\ServiceOrder\Enums\BudgetDecision;
use App\Domain\ServiceOrder\ServiceOrder;
use DateTimeImmutable;

final readonly class ProcessServiceOrderBudgetDecision
{
    public function __construct(
        private ServiceOrderBudgetDecision $budgetDecisions,
        private NotifyServiceOrderStatus $statusNotifications,
    ) {}

    public function execute(
        int $serviceOrderId,
        BudgetDecision $decision,
        DateTimeImmutable $occurredAt,
    ): ServiceOrder {
        $result = $this->budgetDecisions->process($serviceOrderId, $decision, $occurredAt);

        if ($result->transitioned) {
            $this->statusNotifications->execute($result->serviceOrder);
        }

        return $result->serviceOrder;
    }
}
