<?php

namespace App\Interfaces\Http\Requests\ServiceOrder;

use DateTimeImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BudgetDecisionWebhookRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_order_id' => ['required', 'integer', 'min:1'],
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'occurred_at' => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $tolerance = (int) config('services.service_order_webhook.timestamp_tolerance_seconds', 0);

            if ($tolerance <= 0 || $validator->errors()->has('occurred_at')) {
                return;
            }

            $occurredAt = new DateTimeImmutable($this->string('occurred_at')->toString());
            $difference = abs((new DateTimeImmutable)->getTimestamp() - $occurredAt->getTimestamp());

            if ($difference > $tolerance) {
                $validator->errors()->add(
                    'occurred_at',
                    'O instante do webhook esta fora da janela aceita.',
                );
            }
        }];
    }
}
