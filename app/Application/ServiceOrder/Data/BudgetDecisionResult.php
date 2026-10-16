<?php

namespace App\Application\ServiceOrder\Data;

use App\Domain\ServiceOrder\ServiceOrder;

final readonly class BudgetDecisionResult
{
    public function __construct(
        public ServiceOrder $serviceOrder,
        public bool $transitioned,
    ) {}
}
