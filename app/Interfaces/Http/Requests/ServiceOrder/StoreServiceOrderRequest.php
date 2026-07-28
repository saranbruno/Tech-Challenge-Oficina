<?php

namespace App\Interfaces\Http\Requests\ServiceOrder;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_document' => ['required', 'string'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
            'inventory_items' => ['present', 'array'],
            'inventory_items.*.inventory_item_id' => ['required', 'integer', 'distinct', 'exists:inventory_items,id'],
            'inventory_items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
