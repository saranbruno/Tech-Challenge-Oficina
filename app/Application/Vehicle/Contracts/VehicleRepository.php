<?php

namespace App\Application\Vehicle\Contracts;

use App\Domain\Vehicle\Vehicle;

interface VehicleRepository
{
    public function paginate(int $perPage): mixed;

    public function findOrFail(int $id): Vehicle;

    public function licensePlateExists(string $licensePlate, ?int $exceptId = null): bool;

    public function save(Vehicle $vehicle): Vehicle;

    public function delete(int $id): void;
}
