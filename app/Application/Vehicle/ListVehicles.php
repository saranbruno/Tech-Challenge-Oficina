<?php

namespace App\Application\Vehicle;

use App\Application\Shared\Data\PaginatedResult;
use App\Application\Vehicle\Contracts\VehicleRepository;

final readonly class ListVehicles
{
    public function __construct(private VehicleRepository $vehicles) {}

    public function execute(int $perPage): PaginatedResult
    {
        return $this->vehicles->paginate($perPage);
    }
}
