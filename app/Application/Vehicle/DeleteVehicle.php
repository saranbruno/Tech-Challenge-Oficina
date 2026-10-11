<?php

namespace App\Application\Vehicle;

use App\Application\Vehicle\Contracts\VehicleRepository;

final readonly class DeleteVehicle
{
    public function __construct(private VehicleRepository $vehicles) {}

    public function execute(int $id): void
    {
        $this->vehicles->delete($id);
    }
}
