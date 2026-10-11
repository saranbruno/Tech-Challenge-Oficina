<?php

namespace App\Application\Notification;

use App\Application\Notification\Contracts\EmailNotificationSender;
use App\Application\Notification\Contracts\NotificationFailureReporter;
use App\Application\Notification\Contracts\SmsNotificationSender;
use App\Application\Notification\Data\NotificationDeliveryFailure;
use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Application\Notification\Enums\NotificationMedium;
use App\Domain\Customer\ValueObjects\Email;
use App\Domain\Customer\ValueObjects\Phone;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Throwable;

final readonly class DispatchServiceOrderStatusNotification
{
    public function __construct(
        private EmailNotificationSender $emails,
        private SmsNotificationSender $sms,
        private NotificationFailureReporter $failures,
        private ServiceOrderStatusNotificationFactory $notifications,
    ) {}

    public function execute(
        int $serviceOrderId,
        ServiceOrderStatus $status,
        ?Email $email,
        ?Phone $phone,
    ): void {
        $notification = $this->notifications->make($serviceOrderId, $status);

        if ($email !== null) {
            $this->sendEmail($email, $notification);
        }

        if ($phone !== null) {
            $this->sendSms($phone, $notification);
        }
    }

    private function sendEmail(Email $recipient, ServiceOrderStatusNotification $notification): void
    {
        try {
            $this->emails->send($recipient, $notification);
        } catch (Throwable $cause) {
            $this->reportFailure(NotificationMedium::Email, $notification, $cause);
        }
    }

    private function sendSms(Phone $recipient, ServiceOrderStatusNotification $notification): void
    {
        try {
            $this->sms->send($recipient, $notification);
        } catch (Throwable $cause) {
            $this->reportFailure(NotificationMedium::Sms, $notification, $cause);
        }
    }

    private function reportFailure(
        NotificationMedium $medium,
        ServiceOrderStatusNotification $notification,
        Throwable $cause,
    ): void {
        try {
            $this->failures->report(new NotificationDeliveryFailure(
                $medium,
                $notification->serviceOrderId,
                $notification->status,
                $cause,
            ));
        } catch (Throwable) {
        }
    }
}
