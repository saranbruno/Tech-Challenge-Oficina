<?php

namespace App\Domain\Vehicle\ValueObjects;

use App\Domain\Vehicle\Exceptions\InvalidLicensePlate;

final readonly class LicensePlate
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');

        if (! preg_match('/^[A-Z]{3}(?:[0-9]{4}|[0-9][A-Z][0-9]{2})$/', $normalized)) {
            throw new InvalidLicensePlate('A placa deve seguir o padrao brasileiro antigo ou Mercosul.');
        }

        $this->value = $normalized;
    }
}
