<?php

namespace App\Application\Notification\Data;

use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;

final readonly class ServiceOrderStatusNotification
{
    public function __construct(
        public int $serviceOrderId,
        public ServiceOrderStatus $status,
        public string $subject,
        public string $body,
    ) {}
}
