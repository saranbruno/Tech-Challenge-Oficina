<?php

namespace Tests\Unit\Infrastructure\Notification\Sms;

use App\Application\Notification\ServiceOrderStatusNotificationFactory;
use App\Domain\Customer\ValueObjects\Phone;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Infrastructure\Notification\Sms\TwilioSmsNotificationSender;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class TwilioSmsNotificationSenderTest extends TestCase
{
    public function test_posts_the_status_message_to_twilio(): void
    {
        Http::fake();
        config(['notifications.sms.twilio' => [
            'account_sid' => 'AC123',
            'auth_token' => 'secret-token',
            'from' => '+15550000000',
            'timeout' => 4,
            'base_url' => 'https://twilio.test/api',
        ]]);

        $notification = (new ServiceOrderStatusNotificationFactory)->make(72, ServiceOrderStatus::InExecution);
        (new TwilioSmsNotificationSender)->send(new Phone('+5511999999999'), $notification);

        Http::assertSent(function (Request $request) use ($notification): bool {
            return $request->url() === 'https://twilio.test/api/Accounts/AC123/Messages.json'
                && $request['To'] === '+5511999999999'
                && $request['From'] === '+15550000000'
                && $request['Body'] === $notification->body
                && $request->hasHeader('Authorization');
        });
    }

    public function test_rejects_a_message_longer_than_the_sms_limit(): void
    {
        Http::fake();
        config([
            'notifications.sms.twilio' => $this->twilioSettings(),
            'notifications.sms.max_length' => 10,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        (new TwilioSmsNotificationSender)->send(
            new Phone('+5511999999999'),
            new \App\Application\Notification\Data\ServiceOrderStatusNotification(
                73,
                ServiceOrderStatus::Delivered,
                'subject',
                'message longer than ten',
            ),
        );
    }

    public function test_requires_twilio_settings(): void
    {
        Http::fake();
        config(['notifications.sms.twilio' => [
            'account_sid' => '',
            'auth_token' => '',
            'from' => '',
            'timeout' => 10,
            'base_url' => 'https://twilio.test/api',
        ]]);

        $this->expectException(RuntimeException::class);
        (new TwilioSmsNotificationSender)->send(
            new Phone('+5511999999999'),
            (new ServiceOrderStatusNotificationFactory)->make(74, ServiceOrderStatus::Received),
        );
    }

    private function twilioSettings(): array
    {
        return [
            'account_sid' => 'AC123',
            'auth_token' => 'secret-token',
            'from' => '+15550000000',
            'timeout' => 4,
            'base_url' => 'https://twilio.test/api',
        ];
    }
}
