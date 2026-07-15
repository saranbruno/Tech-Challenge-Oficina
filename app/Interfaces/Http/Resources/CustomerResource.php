<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $customer = $this->resource;

        return [
            'id' => $customer instanceof Customer ? $customer->id : $customer->getKey(),
            'name' => $customer->name,
            'document' => $customer instanceof Customer ? $customer->document->value : $customer->document,
            'document_type' => $customer instanceof Customer ? $customer->document->type->value : $customer->document_type,
        ];
    }
}
