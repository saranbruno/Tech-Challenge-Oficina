<?php

namespace App\Interfaces\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity_available' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }
}
