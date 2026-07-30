<?php

namespace App\Interfaces\Http\Requests\ServiceOrder;

use Illuminate\Foundation\Http\FormRequest;

class TrackServiceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_document' => ['required', 'string', 'max:18'],
            'tracking_token' => ['required', 'string', 'size:64', 'regex:/^[a-f0-9]+$/'],
        ];
    }
}
