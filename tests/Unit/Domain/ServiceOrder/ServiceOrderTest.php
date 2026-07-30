<?php

namespace Tests\Unit\Domain\ServiceOrder;

use App\Domain\Service\ValueObjects\UnitPrice;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderBudget;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderTransition;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderService;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ServiceOrderTest extends TestCase
{
    public function test_service_order_starts_received_and_completes_the_valid_flow(): void
    {
        $receivedAt = new DateTimeImmutable('2026-07-22 09:00:00');
        $order = ServiceOrder::receive(10, 20, $receivedAt);
        $order->addService(new ServiceOrderService(1, 1, new UnitPrice(10000)));

        self::assertSame(ServiceOrderStatus::Received, $order->status);
        self::assertSame($receivedAt, $order->receivedAt);

        $diagnosisAt = new DateTimeImmutable('2026-07-22 09:30:00');
        $approvalAt = new DateTimeImmutable('2026-07-22 10:00:00');
        $executionAt = new DateTimeImmutable('2026-07-22 11:00:00');
        $finalizedAt = new DateTimeImmutable('2026-07-22 15:00:00');
        $deliveredAt = new DateTimeImmutable('2026-07-22 17:00:00');

        $order->startDiagnosis($diagnosisAt);
        $order->makeBudgetAvailable($approvalAt);
        $order->approveBudget($executionAt);
        $order->finalize($finalizedAt);
        $order->deliver($deliveredAt);

        self::assertSame(ServiceOrderStatus::Delivered, $order->status);
        self::assertSame($diagnosisAt, $order->diagnosisStartedAt);
        self::assertSame($approvalAt, $order->awaitingApprovalAt);
        self::assertSame($executionAt, $order->executionStartedAt);
        self::assertSame($finalizedAt, $order->finalizedAt);
        self::assertSame($deliveredAt, $order->deliveredAt);
    }

    #[DataProvider('invalidTransitionProvider')]
    public function test_invalid_transitions_are_rejected(callable $transition): void
    {
        $order = ServiceOrder::receive(1, 2, new DateTimeImmutable('2026-07-22 09:00:00'));

        $this->expectException(InvalidServiceOrderTransition::class);

        $transition($order);
    }

    public static function invalidTransitionProvider(): array
    {
        $occurredAt = new DateTimeImmutable('2026-07-22 10:00:00');

        return [
            'disponibilizar sem diagnostico' => [fn (ServiceOrder $order) => $order->makeBudgetAvailable($occurredAt)],
            'aprovar sem orcamento' => [fn (ServiceOrder $order) => $order->approveBudget($occurredAt)],
            'finalizar sem execucao' => [fn (ServiceOrder $order) => $order->finalize($occurredAt)],
            'entregar sem finalizar' => [fn (ServiceOrder $order) => $order->deliver($occurredAt)],
        ];
    }

    public function test_diagnosis_cannot_be_started_twice(): void
    {
        $order = ServiceOrder::receive(1, 2, new DateTimeImmutable('2026-07-22 09:00:00'));
        $order->startDiagnosis(new DateTimeImmutable('2026-07-22 09:30:00'));

        $this->expectException(InvalidServiceOrderTransition::class);

        $order->startDiagnosis(new DateTimeImmutable('2026-07-22 10:00:00'));
    }

    public function test_budget_cannot_be_made_available_without_a_service(): void
    {
        $order = ServiceOrder::receive(1, 2, new DateTimeImmutable('2026-07-22 09:00:00'));
        $order->startDiagnosis(new DateTimeImmutable('2026-07-22 09:30:00'));

        $this->expectException(InvalidServiceOrderBudget::class);

        $order->makeBudgetAvailable(new DateTimeImmutable('2026-07-22 10:00:00'));
    }

    #[DataProvider('cancellableStatusProvider')]
    public function test_order_can_be_cancelled_only_from_confirmed_statuses(ServiceOrderStatus $status): void
    {
        $order = $this->orderAt($status);
        $cancelledAt = new DateTimeImmutable('2026-07-22 12:00:00');

        $order->cancel($cancelledAt);

        self::assertSame(ServiceOrderStatus::Cancelled, $order->status);
        self::assertSame($cancelledAt, $order->cancelledAt);
    }

    public static function cancellableStatusProvider(): array
    {
        return [
            [ServiceOrderStatus::Received],
            [ServiceOrderStatus::InDiagnosis],
            [ServiceOrderStatus::AwaitingApproval],
        ];
    }

    #[DataProvider('nonCancellableStatusProvider')]
    public function test_order_cannot_be_cancelled_after_execution_starts(ServiceOrderStatus $status): void
    {
        $order = $this->orderAt($status);

        $this->expectException(InvalidServiceOrderTransition::class);

        $order->cancel(new DateTimeImmutable('2026-07-22 18:00:00'));
    }

    public static function nonCancellableStatusProvider(): array
    {
        return [
            [ServiceOrderStatus::InExecution],
            [ServiceOrderStatus::Finalized],
            [ServiceOrderStatus::Delivered],
            [ServiceOrderStatus::Cancelled],
        ];
    }

    private function orderAt(ServiceOrderStatus $status): ServiceOrder
    {
        $order = ServiceOrder::receive(1, 2, new DateTimeImmutable('2026-07-22 09:00:00'));
        $occurredAt = new DateTimeImmutable('2026-07-22 10:00:00');

        if ($status === ServiceOrderStatus::Cancelled) {
            $order->cancel($occurredAt);

            return $order;
        }

        if ($status === ServiceOrderStatus::Received) {
            return $order;
        }

        $order->startDiagnosis($occurredAt);

        if ($status === ServiceOrderStatus::InDiagnosis) {
            return $order;
        }

        $order->addService(new ServiceOrderService(1, 1, new UnitPrice(10000)));
        $order->makeBudgetAvailable($occurredAt);

        if ($status === ServiceOrderStatus::AwaitingApproval) {
            return $order;
        }

        $order->approveBudget($occurredAt);

        if ($status === ServiceOrderStatus::InExecution) {
            return $order;
        }

        $order->finalize($occurredAt);

        if ($status === ServiceOrderStatus::Finalized) {
            return $order;
        }

        $order->deliver($occurredAt);

        return $order;
    }
}
