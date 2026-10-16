<?php

namespace App\Application\Notification\Data;

use App\Application\Notification\Enums\NotificationMedium;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Throwable;

final readonly class NotificationDeliveryFailure
{
    public function __construct(
        public NotificationMedium $medium,
        public int $serviceOrderId,
        public ServiceOrderStatus $status,
        public Throwable $cause,
    ) {}
}
