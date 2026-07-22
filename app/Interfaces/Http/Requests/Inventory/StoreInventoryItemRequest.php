<?php

namespace App\Interfaces\Http\Requests\Inventory;

use App\Domain\Inventory\Enums\InventoryItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(InventoryItemType::class)],
            'unit_price' => ['required', 'integer', 'min:0'],
            'quantity_available' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }
}
