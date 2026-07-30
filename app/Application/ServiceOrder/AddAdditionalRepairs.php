<?php

namespace App\Application\ServiceOrder;

use App\Application\Service\Contracts\ServiceRepository;
use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\ServiceOrder\Data\RequestedServiceData;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderService;
use DomainException;

final readonly class AddAdditionalRepairs
{
    public function __construct(
        private ServiceOrderRepository $serviceOrders,
        private ServiceRepository $services,
    ) {}

    public function execute(int $serviceOrderId, array $requestedServices): ServiceOrder
    {
        if ($requestedServices === []) {
            throw new DomainException('Informe ao menos um reparo adicional.');
        }

        $serviceOrder = $this->serviceOrders->findOrFail($serviceOrderId);

        foreach ($requestedServices as $requestedService) {
            if (! $requestedService instanceof RequestedServiceData) {
                throw new DomainException('A composicao de reparos adicionais e invalida.');
            }

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
