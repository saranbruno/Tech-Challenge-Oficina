<?php

namespace App\Domain\ServiceOrder\Enums;

enum ServiceOrderStatus: string
{
    case Received = 'received';
    case InDiagnosis = 'in_diagnosis';
    case AwaitingApproval = 'awaiting_approval';
    case InExecution = 'in_execution';
    case Finalized = 'finalized';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
