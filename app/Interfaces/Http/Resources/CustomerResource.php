<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $customer = $this->customer();

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'document' => $customer->document->value,
            'document_type' => $customer->document->type->value,
            'email' => $customer->email?->value,
            'phone' => $customer->phone?->value,
        ];
    }

    private function customer(): Customer
    {
        return $this->resource;
    }
}
