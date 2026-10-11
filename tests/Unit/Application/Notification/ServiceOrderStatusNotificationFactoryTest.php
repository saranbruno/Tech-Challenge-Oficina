<?php

namespace Tests\Unit\Application\Notification;

use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServiceOrderStatusNotificationFactoryTest extends TestCase
{
    #[DataProvider('statusProvider')]
    public function test_builds_a_minimal_message_for_every_status(
        ServiceOrderStatus $status,
        string $label,
    ): void {
        $notification = (new ServiceOrderStatusNotificationFactory)->make(91, $status);

        self::assertSame(91, $notification->serviceOrderId);
        self::assertSame($status, $notification->status);
        self::assertSame('Atualizacao da ordem de servico #91', $notification->subject);
        self::assertSame("A ordem de servico #91 agora esta com o status: {$label}.", $notification->body);
    }

    public static function statusProvider(): array
    {
        return [
            'received' => [ServiceOrderStatus::Received, 'recebida'],
            'in diagnosis' => [ServiceOrderStatus::InDiagnosis, 'em diagnostico'],
            'awaiting approval' => [ServiceOrderStatus::AwaitingApproval, 'aguardando aprovacao'],
            'in execution' => [ServiceOrderStatus::InExecution, 'em execucao'],
            'finalized' => [ServiceOrderStatus::Finalized, 'finalizada'],
            'delivered' => [ServiceOrderStatus::Delivered, 'entregue'],
            'cancelled' => [ServiceOrderStatus::Cancelled, 'cancelada'],
        ];
    }
}
