<?php

namespace Tests\Unit\Domain\Vehicle;

use App\Domain\Vehicle\Exceptions\InvalidLicensePlate;
use App\Domain\Vehicle\ValueObjects\LicensePlate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LicensePlateTest extends TestCase
{
    #[DataProvider('validLicensePlates')]
    public function test_it_normalizes_and_validates_brazilian_license_plates(string $input, string $normalized): void
    {
        $this->assertSame($normalized, (new LicensePlate($input))->value);
    }

    #[DataProvider('invalidLicensePlates')]
    public function test_it_rejects_invalid_license_plates(string $input): void
    {
        $this->expectException(InvalidLicensePlate::class);

        new LicensePlate($input);
    }

    public static function validLicensePlates(): array
    {
        return [
            ['abc-1234', 'ABC1234'],
            ['bra 1e23', 'BRA1E23'],
        ];
    }

    public static function invalidLicensePlates(): array
    {
        return [
            ['AB12345'],
            ['ABC12D3'],
            ['ABC1D2'],
            ['ABC12345'],
        ];
    }
}
