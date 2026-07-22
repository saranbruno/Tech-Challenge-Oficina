<?php

namespace Tests\Unit\Domain\Inventory;

use App\Domain\Inventory\ValueObjects\StockQuantity;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StockQuantityTest extends TestCase
{
    #[DataProvider('validQuantities')]
    public function test_accepts_non_negative_quantities(int $quantity): void
    {
        $this->assertSame($quantity, new StockQuantity($quantity)->value);
    }

    public function test_rejects_negative_quantity(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('A quantidade em estoque nao pode ser negativa.');

        new StockQuantity(-1);
    }

    public static function validQuantities(): array
    {
        return [[0], [1], [2147483647]];
    }
}
