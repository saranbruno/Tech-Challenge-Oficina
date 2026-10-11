<?php

namespace App\Application\Vehicle;

use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Domain\Vehicle\Vehicle;

final readonly class CreateVehicle
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleDataFactory $factory,
    ) {}

    public function execute(int $customerId, string $licensePlate, string $brand, string $model, int $year): Vehicle
    {
        return $this->vehicles->save($this->factory->make(null, $customerId, $licensePlate, $brand, $model, $year));
    }
}
