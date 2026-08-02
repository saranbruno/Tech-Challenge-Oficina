<?php

namespace Tests\Unit\Domain\ServiceOrder;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderExecutionTimeCalculator;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ServiceOrderExecutionTimeCalculatorTest extends TestCase
{
    #[Test]
    public function it_calculates_full_cycle_and_each_status_average_in_seconds(): void
    {
        $calculator = new ServiceOrderExecutionTimeCalculator;

        $result = $calculator->calculate([
            $this->deliveredOrder([0, 10, 30, 60, 100, 150]),
            $this->deliveredOrder([0, 20, 60, 120, 200, 300], 2),
        ]);

        self::assertSame(2, $result['eligible_orders']);
        self::assertSame(225, $result['average_total_seconds']);
        self::assertSame([
            'received' => 15,
            'in_diagnosis' => 30,
            'awaiting_approval' => 45,
            'in_execution' => 60,
            'finalized' => 75,
        ], $result['average_seconds_by_status']);
    }

    #[Test]
    public function it_returns_null_averages_when_there_are_no_eligible_orders(): void
    {
        $result = (new ServiceOrderExecutionTimeCalculator)->calculate([]);

        self::assertSame(0, $result['eligible_orders']);
        self::assertNull($result['average_total_seconds']);
        self::assertSame([
            'received' => null,
            'in_diagnosis' => null,
            'awaiting_approval' => null,
            'in_execution' => null,
            'finalized' => null,
        ], $result['average_seconds_by_status']);
    }

    #[Test]
    public function it_ignores_orders_without_every_required_instant(): void
    {
        $incomplete = ServiceOrder::reconstitute(
            1,
            1,
            1,
            ServiceOrderStatus::Delivered,
            new DateTimeImmutable('@0'),
            new DateTimeImmutable('@10'),
            null,
            new DateTimeImmutable('@30'),
            new DateTimeImmutable('@40'),
            new DateTimeImmutable('@50'),
            null,
        );

        $result = (new ServiceOrderExecutionTimeCalculator)->calculate([$incomplete]);

        self::assertSame(0, $result['eligible_orders']);
        self::assertNull($result['average_total_seconds']);
    }

    private function deliveredOrder(array $offsets, int $id = 1): ServiceOrder
    {
        $instant = fn (int $offset): DateTimeImmutable => (new DateTimeImmutable('@0'))->modify("+{$offset} seconds");

        return ServiceOrder::reconstitute(
            $id,
            $id,
            $id,
            ServiceOrderStatus::Delivered,
            $instant($offsets[0]),
            $instant($offsets[1]),
            $instant($offsets[2]),
            $instant($offsets[3]),
            $instant($offsets[4]),
            $instant($offsets[5]),
            null,
        );
    }
}
