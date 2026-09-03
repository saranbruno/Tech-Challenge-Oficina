<?php

namespace Tests\Unit\Infrastructure\Notification\Sms;

use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\Customer\ValueObjects\Phone;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Infrastructure\Notification\Sms\LoggingSmsNotificationSender;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

final class LoggingSmsNotificationSenderTest extends TestCase
{
    public function test_logs_a_simulated_attempt_without_recipient_or_body(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once()->with('SMS notification simulated.', [
            'service_order_id' => 71,
            'status' => 'finalized',
        ]);

        (new LoggingSmsNotificationSender($logger))->send(
            new Phone('+5511999999999'),
            (new ServiceOrderStatusNotificationFactory)->make(71, ServiceOrderStatus::Finalized),
        );
    }
}
