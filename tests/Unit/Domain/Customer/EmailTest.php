<?php

namespace Tests\Unit\Domain\Customer;

use App\Domain\Customer\Exceptions\InvalidEmail;
use App\Domain\Customer\ValueObjects\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function test_email_is_trimmed_and_normalized_to_lowercase(): void
    {
        self::assertSame('cliente@example.com', (new Email('  Cliente@Example.COM  '))->value);
    }

    #[DataProvider('invalidEmailProvider')]
    public function test_invalid_email_is_rejected(string $value): void
    {
        $this->expectException(InvalidEmail::class);

        new Email($value);
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'vazio' => [''],
            'sem dominio' => ['cliente@'],
            'sem arroba' => ['cliente.example.com'],
            'acima do limite' => [str_repeat('a', 245).'@example.com'],
        ];
    }
}
