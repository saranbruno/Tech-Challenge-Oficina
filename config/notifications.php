<?php

return [
    'failure_log_channel' => env('NOTIFICATION_FAILURE_LOG_CHANNEL', 'stack'),
    'email' => [
        'mailer' => env('NOTIFICATION_MAILER', env('MAIL_MAILER', 'log')),
    ],
    'sms' => [
        'driver' => env('NOTIFICATION_SMS_DRIVER', 'log'),
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
            'timeout' => (int) env('TWILIO_TIMEOUT', 10),
            'base_url' => env('TWILIO_BASE_URL', 'https://api.twilio.com/2010-04-01'),
        ],
        'max_length' => 160,
    ],
];
