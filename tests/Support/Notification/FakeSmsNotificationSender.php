<?php

namespace Tests\Support\Notification;

use App\Application\Notification\Contracts\SmsNotificationSender;
use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Phone;
use RuntimeException;

final class FakeSmsNotificationSender implements SmsNotificationSender
{
    public array $deliveries = [];

    public function __construct(private readonly bool $shouldFail = false) {}

    public function send(Phone $recipient, ServiceOrderStatusNotification $notification): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException('sms failure');
        }

        $this->deliveries[] = [$recipient, $notification];
    }
}
