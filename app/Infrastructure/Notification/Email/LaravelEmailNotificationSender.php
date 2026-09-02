<?php

namespace App\Infrastructure\Notification\Email;

use App\Application\Notification\Contracts\EmailNotificationSender;
use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Email;
use Illuminate\Support\Facades\Mail;

final class LaravelEmailNotificationSender implements EmailNotificationSender
{
    public function send(Email $recipient, ServiceOrderStatusNotification $notification): void
    {
        Mail::mailer((string) config('notifications.email.mailer'))
            ->to($recipient->value)
            ->send(new ServiceOrderStatusMail($notification));
    }
}
