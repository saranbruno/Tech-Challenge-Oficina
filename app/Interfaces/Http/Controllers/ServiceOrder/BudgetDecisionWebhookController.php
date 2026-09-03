<?php

namespace App\Interfaces\Http\Controllers\ServiceOrder;

use App\Interfaces\Http\Requests\ServiceOrder\BudgetDecisionWebhookRequest;
use Illuminate\Http\JsonResponse;

class BudgetDecisionWebhookController
{
    public function __invoke(BudgetDecisionWebhookRequest $request): JsonResponse
    {
        return response()->json(['data' => ['accepted' => true]], 202);
    }
}
