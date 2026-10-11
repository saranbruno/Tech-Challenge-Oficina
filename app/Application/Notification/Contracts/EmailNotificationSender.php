<?php

namespace App\Application\Notification\Contracts;

use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Email;

interface EmailNotificationSender
{
    public function send(Email $recipient, ServiceOrderStatusNotification $notification): void;
}
