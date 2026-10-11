<?php

namespace App\Interfaces\Http\Resources;

use App\Domain\Vehicle\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $vehicle = $this->vehicle();

        return [
            'id' => $vehicle->id,
            'customer_id' => $vehicle->customerId,
            'license_plate' => $vehicle->licensePlate->value,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
        ];
    }

    private function vehicle(): Vehicle
    {
        return $this->resource;
    }
}
