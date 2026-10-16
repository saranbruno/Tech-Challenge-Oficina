<?php

use App\Interfaces\Http\Controllers\ServiceOrder\BudgetDecisionWebhookController;
use App\Interfaces\Http\Middleware\VerifyServiceOrderWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::post('service-orders/budget-decision', BudgetDecisionWebhookController::class)
    ->middleware(VerifyServiceOrderWebhookSignature::class)
    ->name('service-orders.budget-decision');
