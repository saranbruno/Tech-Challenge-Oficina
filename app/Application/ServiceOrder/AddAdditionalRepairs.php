<?php

namespace App\Application\ServiceOrder;

use App\Application\Service\Contracts\ServiceRepository;
use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\ServiceOrder\Data\RequestedServiceCollection;
use App\Domain\ServiceOrder\Exceptions\InvalidAdditionalRepair;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderService;

final readonly class AddAdditionalRepairs
{
    public function __construct(
        private ServiceOrderRepository $serviceOrders,
        private ServiceRepository $services,
    ) {}

    public function execute(int $serviceOrderId, RequestedServiceCollection $requestedServices): ServiceOrder
    {
        if ($requestedServices->isEmpty()) {
            throw new InvalidAdditionalRepair('Informe ao menos um reparo adicional.');
        }

        $serviceOrder = $this->serviceOrders->findOrFail($serviceOrderId);

        foreach ($requestedServices->all() as $requestedService) {
            $service = $this->services->findOrFail($requestedService->serviceId);
            $serviceOrder->addAdditionalService(new ServiceOrderService(
                $service->id,
                $requestedService->quantity,
                $service->unitPrice,
            ));
        }

        return $this->serviceOrders->update($serviceOrder);
    }
}
