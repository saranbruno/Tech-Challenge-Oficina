<?php

namespace Tests\Unit\Infrastructure\Notification\Email;

use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\Customer\ValueObjects\Email;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Infrastructure\Notification\Email\LaravelEmailNotificationSender;
use App\Infrastructure\Notification\Email\ServiceOrderStatusMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class LaravelEmailNotificationSenderTest extends TestCase
{
    public function test_sends_the_status_mailable_to_the_normalized_email(): void
    {
        Mail::fake();
        config(['notifications.email.mailer' => 'array']);
        $notification = (new ServiceOrderStatusNotificationFactory)->make(61, ServiceOrderStatus::Finalized);

        (new LaravelEmailNotificationSender)->send(new Email(' CLIENT@EXAMPLE.COM '), $notification);

        Mail::assertSent(ServiceOrderStatusMail::class, function (ServiceOrderStatusMail $mail) use ($notification): bool {
            return $mail->hasTo('client@example.com')
                && $mail->notification === $notification;
        });
    }

    public function test_renders_the_status_subject_and_body(): void
    {
        $notification = (new ServiceOrderStatusNotificationFactory)->make(62, ServiceOrderStatus::Delivered);
        $mail = new ServiceOrderStatusMail($notification);

        self::assertSame($notification->subject, $mail->envelope()->subject);
        self::assertSame('mail.service-order-status', $mail->content()->view);
        self::assertSame($notification, $mail->content()->with['notification']);
        self::assertSame([], $mail->attachments());
    }
}
