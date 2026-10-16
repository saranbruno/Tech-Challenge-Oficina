<?php

namespace App\Application\ServiceOrder\Contracts;

use App\Application\ServiceOrder\Data\BudgetDecisionResult;
use App\Application\ServiceOrder\Enums\BudgetDecision;
use DateTimeImmutable;

interface ServiceOrderBudgetDecision
{
    public function process(
        int $serviceOrderId,
        BudgetDecision $decision,
        DateTimeImmutable $occurredAt,
    ): BudgetDecisionResult;
}
