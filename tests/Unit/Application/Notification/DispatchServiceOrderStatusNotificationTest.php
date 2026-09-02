<?php

namespace Tests\Unit\Application\Notification;

use App\Application\Notification\DispatchServiceOrderStatusNotification;
use App\Application\Notification\Enums\NotificationMedium;
use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\Customer\ValueObjects\Email;
use App\Domain\Customer\ValueObjects\Phone;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\Notification\FakeEmailNotificationSender;
use Tests\Support\Notification\FakeNotificationFailureReporter;
use Tests\Support\Notification\FakeSmsNotificationSender;

final class DispatchServiceOrderStatusNotificationTest extends TestCase
{
    #[DataProvider('contactProvider')]
    public function test_dispatches_to_every_available_contact(
        ?Email $email,
        ?Phone $phone,
        int $expectedEmails,
        int $expectedSms,
    ): void {
        $emails = new FakeEmailNotificationSender;
        $sms = new FakeSmsNotificationSender;
        $failures = new FakeNotificationFailureReporter;

        $this->dispatcher($emails, $sms, $failures)->execute(
            42,
            ServiceOrderStatus::InDiagnosis,
            $email,
            $phone,
        );

        self::assertCount($expectedEmails, $emails->deliveries);
        self::assertCount($expectedSms, $sms->deliveries);
        self::assertSame([], $failures->failures);
    }

    public static function contactProvider(): array
    {
        return [
            'no contacts' => [null, null, 0, 0],
            'email only' => [new Email('client@example.com'), null, 1, 0],
            'phone only' => [null, new Phone('+5511999999999'), 0, 1],
            'both contacts' => [new Email('client@example.com'), new Phone('+5511999999999'), 1, 1],
        ];
    }

    public function test_email_failure_does_not_prevent_sms_attempt(): void
    {
        $emails = new FakeEmailNotificationSender(true);
        $sms = new FakeSmsNotificationSender;
        $failures = new FakeNotificationFailureReporter;

        $this->dispatcher($emails, $sms, $failures)->execute(
            43,
            ServiceOrderStatus::AwaitingApproval,
            new Email('client@example.com'),
            new Phone('+5511999999999'),
        );

        self::assertSame([], $emails->deliveries);
        self::assertCount(1, $sms->deliveries);
        self::assertCount(1, $failures->failures);
        self::assertSame(NotificationMedium::Email, $failures->failures[0]->medium);
        self::assertSame(43, $failures->failures[0]->serviceOrderId);
        self::assertSame(ServiceOrderStatus::AwaitingApproval, $failures->failures[0]->status);
    }

    public function test_sms_failure_is_reported_after_email_attempt(): void
    {
        $emails = new FakeEmailNotificationSender;
        $sms = new FakeSmsNotificationSender(true);
        $failures = new FakeNotificationFailureReporter;

        $this->dispatcher($emails, $sms, $failures)->execute(
            44,
            ServiceOrderStatus::Finalized,
            new Email('client@example.com'),
            new Phone('+5511999999999'),
        );

        self::assertCount(1, $emails->deliveries);
        self::assertSame([], $sms->deliveries);
        self::assertCount(1, $failures->failures);
        self::assertSame(NotificationMedium::Sms, $failures->failures[0]->medium);
    }

    public function test_failure_reporter_failure_does_not_prevent_the_other_channel(): void
    {
        $emails = new FakeEmailNotificationSender(true);
        $sms = new FakeSmsNotificationSender;
        $failures = new FakeNotificationFailureReporter(true);

        $this->dispatcher($emails, $sms, $failures)->execute(
            45,
            ServiceOrderStatus::Delivered,
            new Email('client@example.com'),
            new Phone('+5511999999999'),
        );

        self::assertCount(1, $sms->deliveries);
    }

    private function dispatcher(
        FakeEmailNotificationSender $emails,
        FakeSmsNotificationSender $sms,
        FakeNotificationFailureReporter $failures,
    ): DispatchServiceOrderStatusNotification {
        return new DispatchServiceOrderStatusNotification(
            $emails,
            $sms,
            $failures,
            new ServiceOrderStatusNotificationFactory,
        );
    }
}
