<?php

namespace App\Application\Vehicle;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Application\Vehicle\Exceptions\DuplicateLicensePlate;
use App\Domain\Vehicle\ValueObjects\LicensePlate;
use App\Domain\Vehicle\Vehicle;

final readonly class VehicleService
{
    public function __construct(
        private VehicleRepository $vehicles,
        private CustomerRepository $customers,
    ) {}

    public function list(int $perPage): mixed
    {
        return $this->vehicles->paginate($perPage);
    }

    public function find(int $id): Vehicle
    {
        return $this->vehicles->findOrFail($id);
    }

    public function create(int $customerId, string $licensePlate, string $brand, string $model, int $year): Vehicle
    {
        return $this->persist(null, $customerId, $licensePlate, $brand, $model, $year);
    }

    public function update(int $id, int $customerId, string $licensePlate, string $brand, string $model, int $year): Vehicle
    {
        $this->vehicles->findOrFail($id);

        return $this->persist($id, $customerId, $licensePlate, $brand, $model, $year);
    }

    public function delete(int $id): void
    {
        $this->vehicles->delete($id);
    }

    private function persist(?int $id, int $customerId, string $licensePlate, string $brand, string $model, int $year): Vehicle
    {
        $this->customers->findOrFail($customerId);
        $normalizedLicensePlate = new LicensePlate($licensePlate);

        if ($this->vehicles->licensePlateExists($normalizedLicensePlate->value, $id)) {
            throw new DuplicateLicensePlate('Ja existe um veiculo com esta placa.');
        }

        return $this->vehicles->save(new Vehicle(
            $id,
            $customerId,
            $normalizedLicensePlate,
            trim($brand),
            trim($model),
            $year,
        ));
    }
}
