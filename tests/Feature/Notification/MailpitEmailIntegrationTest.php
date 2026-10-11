<?php

namespace Tests\Feature\Notification;

use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\Customer\ValueObjects\Email;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Infrastructure\Notification\Email\LaravelEmailNotificationSender;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class MailpitEmailIntegrationTest extends TestCase
{
    public function test_delivers_a_status_email_to_mailpit(): void
    {
        if (getenv('MAILPIT_INTEGRATION_TEST') !== 'true') {
            self::markTestSkipped('MAILPIT_INTEGRATION_TEST=true is required.');
        }

        config([
            'notifications.email.mailer' => 'smtp',
            'mail.mailers.smtp.host' => env('MAILPIT_HOST', 'mailpit'),
            'mail.mailers.smtp.port' => (int) env('MAILPIT_PORT', 1025),
            'mail.mailers.smtp.scheme' => null,
            'mail.from.address' => 'oficina@example.test',
        ]);
        $notification = (new ServiceOrderStatusNotificationFactory)->make(63, ServiceOrderStatus::InExecution);

        (new LaravelEmailNotificationSender)->send(new Email('mailpit@example.test'), $notification);

        $messages = Http::retry(5, 200)->get(
            rtrim((string) env('MAILPIT_URL', 'http://mailpit:8025'), '/').'/api/v1/messages',
        )->json('messages', []);

        self::assertTrue(collect($messages)->contains(
            fn (array $message): bool => ($message['Subject'] ?? '') === $notification->subject,
        ));
    }
}
