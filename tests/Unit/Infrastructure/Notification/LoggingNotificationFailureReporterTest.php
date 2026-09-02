<?php

namespace Tests\Unit\Infrastructure\Notification;

use App\Application\Notification\Data\NotificationDeliveryFailure;
use App\Application\Notification\Enums\NotificationMedium;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Infrastructure\Notification\LoggingNotificationFailureReporter;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;

final class LoggingNotificationFailureReporterTest extends TestCase
{
    public function test_logs_failure_context_without_recipient_or_exception_message(): void
    {
        $logger = new class extends AbstractLogger
        {
            public array $records = [];

            public function log($level, Stringable|string $message, array $context = []): void
            {
                $this->records[] = [$level, $message, $context];
            }
        };

        (new LoggingNotificationFailureReporter($logger))->report(new NotificationDeliveryFailure(
            NotificationMedium::Sms,
            51,
            ServiceOrderStatus::InExecution,
            new RuntimeException('provider secret response'),
        ));

        self::assertSame('warning', $logger->records[0][0]);
        self::assertSame('notification_delivery_failed', $logger->records[0][1]);
        self::assertSame([
            'medium' => 'sms',
            'service_order_id' => 51,
            'status' => 'in_execution',
            'exception' => RuntimeException::class,
        ], $logger->records[0][2]);
        self::assertStringNotContainsString('provider secret response', serialize($logger->records));
    }
}
