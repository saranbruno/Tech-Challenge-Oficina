<?php

namespace App\Application\Notification\Contracts;

use App\Application\Notification\Data\NotificationDeliveryFailure;

interface NotificationFailureReporter
{
    public function report(NotificationDeliveryFailure $failure): void;
}
