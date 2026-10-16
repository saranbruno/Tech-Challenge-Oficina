<?php

namespace App\Infrastructure\Notification\Sms;

use App\Application\Notification\Contracts\SmsNotificationSender;
use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Phone;
use Psr\Log\LoggerInterface;

final readonly class LoggingSmsNotificationSender implements SmsNotificationSender
{
    public function __construct(private LoggerInterface $logger) {}

    public function send(Phone $recipient, ServiceOrderStatusNotification $notification): void
    {
        $this->logger->info('SMS notification simulated.', [
            'service_order_id' => $notification->serviceOrderId,
            'status' => $notification->status->value,
        ]);
    }
}
