<?php

namespace Tests\Unit\Domain\Service;

use App\Domain\Service\ValueObjects\UnitPrice;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UnitPriceTest extends TestCase
{
    #[DataProvider('validPrices')]
    public function test_accepts_exact_non_negative_integer_prices(int $cents): void
    {
        $price = new UnitPrice($cents);

        $this->assertSame($cents, $price->cents);
    }

    public function test_rejects_negative_price(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('O valor unitario nao pode ser negativo.');

        new UnitPrice(-1);
    }

    public static function validPrices(): array
    {
        return [
            'zero' => [0],
            'one cent' => [1],
            'oil change' => [15990],
        ];
    }
}
