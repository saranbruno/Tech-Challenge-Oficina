<?php

namespace App\Application\Notification\Contracts;

use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Phone;

interface SmsNotificationSender
{
    public function send(Phone $recipient, ServiceOrderStatusNotification $notification): void;
}
