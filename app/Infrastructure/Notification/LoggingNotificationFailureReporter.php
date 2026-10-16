<?php

namespace App\Infrastructure\Notification;

use App\Application\Notification\Contracts\NotificationFailureReporter;
use App\Application\Notification\Data\NotificationDeliveryFailure;
use Psr\Log\LoggerInterface;

final readonly class LoggingNotificationFailureReporter implements NotificationFailureReporter
{
    public function __construct(private LoggerInterface $logger) {}

    public function report(NotificationDeliveryFailure $failure): void
    {
        $this->logger->warning('notification_delivery_failed', [
            'medium' => $failure->medium->value,
            'service_order_id' => $failure->serviceOrderId,
            'status' => $failure->status->value,
            'exception' => $failure->cause::class,
        ]);
    }
}
