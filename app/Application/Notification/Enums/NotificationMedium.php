<?php

namespace App\Application\Notification\Enums;

enum NotificationMedium: string
{
    case Email = 'email';
    case Sms = 'sms';
}
