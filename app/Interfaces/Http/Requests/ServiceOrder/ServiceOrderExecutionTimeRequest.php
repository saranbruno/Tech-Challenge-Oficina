<?php

namespace App\Interfaces\Http\Requests\ServiceOrder;

use Illuminate\Foundation\Http\FormRequest;

class ServiceOrderExecutionTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivered_from' => ['nullable', 'date_format:Y-m-d'],
            'delivered_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:delivered_from'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ];
    }
}
