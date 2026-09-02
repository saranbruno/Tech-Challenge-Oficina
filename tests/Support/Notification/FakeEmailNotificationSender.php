<?php

namespace Tests\Support\Notification;

use App\Application\Notification\Contracts\EmailNotificationSender;
use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Email;
use RuntimeException;

final class FakeEmailNotificationSender implements EmailNotificationSender
{
    public array $deliveries = [];

    public function __construct(private readonly bool $shouldFail = false) {}

    public function send(Email $recipient, ServiceOrderStatusNotification $notification): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException('email failure');
        }

        $this->deliveries[] = [$recipient, $notification];
    }
}
