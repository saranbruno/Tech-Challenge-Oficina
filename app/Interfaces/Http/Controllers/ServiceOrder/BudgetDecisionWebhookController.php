<?php

namespace App\Interfaces\Http\Controllers\ServiceOrder;

use App\Application\ServiceOrder\Enums\BudgetDecision;
use App\Application\ServiceOrder\ProcessServiceOrderBudgetDecision;
use App\Interfaces\Http\Requests\ServiceOrder\BudgetDecisionWebhookRequest;
use Illuminate\Http\JsonResponse;

class BudgetDecisionWebhookController
{
    public function __construct(private readonly ProcessServiceOrderBudgetDecision $processBudgetDecision) {}

    public function __invoke(BudgetDecisionWebhookRequest $request): JsonResponse
    {
        $serviceOrder = $this->processBudgetDecision->execute(
            $request->integer('service_order_id'),
            BudgetDecision::from($request->string('decision')->toString()),
            new \DateTimeImmutable($request->string('occurred_at')->toString()),
        );

        return response()->json(['data' => [
            'service_order_id' => $serviceOrder->id,
            'status' => $serviceOrder->status->value,
        ]]);
    }
}
