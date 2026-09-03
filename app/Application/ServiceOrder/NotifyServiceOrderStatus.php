<?php

namespace App\Application\ServiceOrder;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Notification\DispatchServiceOrderStatusNotification;
use App\Domain\ServiceOrder\ServiceOrder;

final readonly class NotifyServiceOrderStatus
{
    public function __construct(
        private CustomerRepository $customers,
        private DispatchServiceOrderStatusNotification $notifications,
    ) {}

    public function execute(ServiceOrder $serviceOrder): void
    {
        $customer = $this->customers->findOrFail($serviceOrder->customerId);

        $this->notifications->execute(
            $serviceOrder->id,
            $serviceOrder->status,
            $customer->email,
            $customer->phone,
        );
    }
}
