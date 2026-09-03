<?php

namespace App\Application\ServiceOrder\Enums;

enum BudgetDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
