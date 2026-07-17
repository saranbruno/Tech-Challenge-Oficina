<?php

namespace App\Domain\Vehicle;

use App\Domain\Vehicle\ValueObjects\LicensePlate;
use DomainException;

final readonly class Vehicle
{
    public function __construct(
        public ?int $id,
        public int $customerId,
        public LicensePlate $licensePlate,
        public string $brand,
        public string $model,
        public int $year,
    ) {
        if ($year < 1886 || $year > (int) date('Y') + 1) {
            throw new DomainException('O ano do veiculo deve estar entre 1886 e o proximo ano.');
        }
    }
}
