<?php

namespace Tests\Support\Notification;

use App\Application\Notification\Contracts\NotificationFailureReporter;
use App\Application\Notification\Data\NotificationDeliveryFailure;
use RuntimeException;

final class FakeNotificationFailureReporter implements NotificationFailureReporter
{
    public array $failures = [];

    public function __construct(private readonly bool $shouldFail = false) {}

    public function report(NotificationDeliveryFailure $failure): void
    {
        if ($this->shouldFail) {
            throw new RuntimeException('reporting failure');
        }

        $this->failures[] = $failure;
    }
}
