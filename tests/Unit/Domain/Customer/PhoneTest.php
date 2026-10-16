<?php

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\Exceptions\InvalidPhone;
use App\Domain\Customer\ValueObjects\Phone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    #[DataProvider('validPhoneProvider')]
    public function test_phone_is_normalized_to_e164(string $value, string $expected): void
    {
        self::assertSame($expected, (new Phone($value))->value);
    }

    public static function validPhoneProvider(): array
    {
        return [
            'brasileiro com formatacao' => ['(11) 99876-5432', '+5511998765432'],
            'brasileiro com codigo do pais' => ['55 11 99876-5432', '+5511998765432'],
            'internacional e164' => ['+1 (202) 555-0123', '+12025550123'],
        ];
    }

    #[DataProvider('invalidPhoneProvider')]
    public function test_invalid_phone_is_rejected(string $value): void
    {
        $this->expectException(InvalidPhone::class);

        new Phone($value);
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            'vazio' => [''],
            'curto' => ['12345'],
            'codigo iniciado por zero' => ['+05511998765432'],
            'caracteres invalidos' => ['+55 telefone'],
        ];
    }
}
