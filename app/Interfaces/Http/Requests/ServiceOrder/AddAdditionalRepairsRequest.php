<?php

namespace App\Interfaces\Http\Requests\ServiceOrder;

use Illuminate\Foundation\Http\FormRequest;

class AddAdditionalRepairsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
