<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Vehicle\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $vehicle = $this->resource;

        return [
            'id' => $vehicle instanceof Vehicle ? $vehicle->id : $vehicle->getKey(),
            'customer_id' => $vehicle instanceof Vehicle ? $vehicle->customerId : $vehicle->customer_id,
            'license_plate' => $vehicle instanceof Vehicle ? $vehicle->licensePlate->value : $vehicle->license_plate,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
        ];
    }
}
