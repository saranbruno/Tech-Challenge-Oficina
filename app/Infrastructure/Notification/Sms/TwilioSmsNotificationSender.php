<?php

namespace App\Infrastructure\Notification\Sms;

use App\Application\Notification\Contracts\SmsNotificationSender;
use App\Application\Notification\Data\ServiceOrderStatusNotification;
use App\Domain\Customer\ValueObjects\Phone;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

final class TwilioSmsNotificationSender implements SmsNotificationSender
{
    public function send(Phone $recipient, ServiceOrderStatusNotification $notification): void
    {
        $settings = config('notifications.sms.twilio');
        $message = $notification->body;
        $maxLength = (int) config('notifications.sms.max_length', 160);

        if (! preg_match('/^\+[1-9]\d{7,14}$/', $recipient->value)) {
            throw new InvalidArgumentException('SMS recipient must be an E.164 phone number.');
        }

        if (mb_strlen($message) > $maxLength) {
            throw new InvalidArgumentException('SMS notification exceeds the configured length.');
        }

        foreach (['account_sid', 'auth_token', 'from'] as $setting) {
            if (! is_string($settings[$setting] ?? null) || trim($settings[$setting]) === '') {
                throw new RuntimeException('Twilio SMS configuration is incomplete.');
            }
        }

        $url = rtrim((string) $settings['base_url'], '/')
            .'/Accounts/'.rawurlencode($settings['account_sid']).'/Messages.json';

        Http::asForm()
            ->withBasicAuth($settings['account_sid'], $settings['auth_token'])
            ->timeout((int) $settings['timeout'])
            ->post($url, [
                'To' => $recipient->value,
                'From' => $settings['from'],
                'Body' => $message,
            ])
            ->throw();
    }
}
