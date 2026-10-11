<?php

namespace App\Application\Vehicle;

use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Domain\Vehicle\Vehicle;

final readonly class UpdateVehicle
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleDataFactory $factory,
    ) {}

    public function execute(int $id, int $customerId, string $licensePlate, string $brand, string $model, int $year): Vehicle
    {
        $this->vehicles->findOrFail($id);

        return $this->vehicles->save($this->factory->make($id, $customerId, $licensePlate, $brand, $model, $year));
    }
}
