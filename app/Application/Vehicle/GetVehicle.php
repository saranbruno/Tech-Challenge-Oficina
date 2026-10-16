<?php

namespace App\Application\Vehicle;

use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Domain\Vehicle\Vehicle;

final readonly class GetVehicle
{
    public function __construct(private VehicleRepository $vehicles) {}

    public function execute(int $id): Vehicle
    {
        return $this->vehicles->findOrFail($id);
    }
}
