<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Service\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = $this->service();

        return [
            'id' => $service->id,
            'name' => $service->name,
            'unit_price' => $service->unitPrice->cents,
        ];
    }

    private function service(): Service
    {
        return $this->resource;
    }
}
