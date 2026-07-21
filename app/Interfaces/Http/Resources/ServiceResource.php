<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Service\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = $this->resource;

        return [
            'id' => $service instanceof Service ? $service->id : $service->getKey(),
            'name' => $service->name,
            'unit_price' => $service instanceof Service ? $service->unitPrice->cents : $service->unit_price,
        ];
    }
}
