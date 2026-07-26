<?php

namespace App\Application\ServiceOrder;

use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Service\Contracts\ServiceRepository;
use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\ServiceOrder\Data\RequestedServiceData;
use App\Application\ServiceOrder\Exceptions\VehicleDoesNotBelongToCustomer;
use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Domain\Customer\ValueObjects\Document;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderService;
use DateTimeImmutable;
use DomainException;

final readonly class CreateInitialServiceOrder
{
    public function __construct(
        private CustomerRepository $customers,
        private VehicleRepository $vehicles,
        private ServiceRepository $services,
        private ServiceOrderRepository $serviceOrders,
    ) {}

    public function execute(
        string $customerDocument,
        int $vehicleId,
        array $requestedServices,
        DateTimeImmutable $receivedAt,
    ): ServiceOrder {
        if ($requestedServices === []) {
            throw new DomainException('A ordem de servico deve possuir ao menos um servico.');
        }

        $document = new Document($customerDocument);
        $customer = $this->customers->findByDocumentOrFail($document->value);
        $vehicle = $this->vehicles->findOrFail($vehicleId);

        if ($vehicle->customerId !== $customer->id) {
            throw new VehicleDoesNotBelongToCustomer('O veiculo informado nao pertence ao cliente identificado.');
        }

        $serviceOrder = ServiceOrder::receive($customer->id, $vehicle->id, $receivedAt);

        foreach ($requestedServices as $requestedService) {
            if (! $requestedService instanceof RequestedServiceData) {
                throw new DomainException('A composicao de servicos informada e invalida.');
            }

            $service = $this->services->findOrFail($requestedService->serviceId);
            $serviceOrder->addService(new ServiceOrderService(
                $service->id,
                $requestedService->quantity,
                $service->unitPrice,
            ));
        }

        return $this->serviceOrders->create($serviceOrder);
    }
}
