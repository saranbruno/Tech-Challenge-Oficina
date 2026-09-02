<?php

return [
    'failure_log_channel' => env('NOTIFICATION_FAILURE_LOG_CHANNEL', 'stack'),
    'email' => [
        'mailer' => env('NOTIFICATION_MAILER', env('MAIL_MAILER', 'log')),
    ],
];
