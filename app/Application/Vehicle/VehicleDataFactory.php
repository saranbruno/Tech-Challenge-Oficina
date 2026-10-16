<?php

namespace App\Application\Vehicle;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Application\Vehicle\Exceptions\DuplicateLicensePlate;
use App\Domain\Vehicle\ValueObjects\LicensePlate;
use App\Domain\Vehicle\Vehicle;

final readonly class VehicleDataFactory
{
    public function __construct(
        private VehicleRepository $vehicles,
        private CustomerRepository $customers,
    ) {}

    public function make(
        ?int $id,
        int $customerId,
        string $licensePlate,
        string $brand,
        string $model,
        int $year,
    ): Vehicle {
        $this->customers->findOrFail($customerId);
        $normalizedLicensePlate = new LicensePlate($licensePlate);

        if ($this->vehicles->licensePlateExists($normalizedLicensePlate->value, $id)) {
            throw new DuplicateLicensePlate('Ja existe um veiculo com esta placa.');
        }

        return new Vehicle($id, $customerId, $normalizedLicensePlate, trim($brand), trim($model), $year);
    }
}
