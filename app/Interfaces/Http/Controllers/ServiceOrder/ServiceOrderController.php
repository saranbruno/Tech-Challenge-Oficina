<?php

namespace App\Interfaces\Http\Controllers\ServiceOrder;

use App\Application\ServiceOrder\AddAdditionalRepairs;
use App\Application\ServiceOrder\CancelServiceOrder;
use App\Application\ServiceOrder\CompleteServiceOrderDiagnosis;
use App\Application\ServiceOrder\CreateServiceOrder;
use App\Application\ServiceOrder\Data\RequestedInventoryItemData;
use App\Application\ServiceOrder\Data\RequestedServiceData;
use App\Application\ServiceOrder\StartServiceOrderDiagnosis;
use App\Interfaces\Http\Requests\ServiceOrder\AddAdditionalRepairsRequest;
use App\Interfaces\Http\Requests\ServiceOrder\StoreServiceOrderRequest;
use App\Interfaces\Http\Resources\ServiceOrderResource;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;

class ServiceOrderController
{
    public function __construct(
        private readonly AddAdditionalRepairs $addAdditionalRepairs,
        private readonly CancelServiceOrder $cancelServiceOrder,
        private readonly CompleteServiceOrderDiagnosis $completeServiceOrderDiagnosis,
        private readonly CreateServiceOrder $createServiceOrder,
        private readonly StartServiceOrderDiagnosis $startServiceOrderDiagnosis,
    ) {}

    public function store(StoreServiceOrderRequest $request): JsonResponse
    {
        $order = $this->createServiceOrder->execute(
            $request->string('customer_document')->toString(),
            $request->integer('vehicle_id'),
            array_map(
                fn (array $service): RequestedServiceData => new RequestedServiceData(
                    $service['service_id'],
                    $service['quantity'],
                ),
                $request->validated('services'),
            ),
            array_map(
                fn (array $inventoryItem): RequestedInventoryItemData => new RequestedInventoryItemData(
                    $inventoryItem['inventory_item_id'],
                    $inventoryItem['quantity'],
                ),
                $request->validated('inventory_items'),
            ),
            new DateTimeImmutable,
        );

        return (new ServiceOrderResource($order))->response()->setStatusCode(201);
    }

    public function startDiagnosis(int $serviceOrder): ServiceOrderResource
    {
        return new ServiceOrderResource($this->startServiceOrderDiagnosis->execute(
            $serviceOrder,
            new DateTimeImmutable,
        ));
    }

    public function completeDiagnosis(int $serviceOrder): ServiceOrderResource
    {
        return new ServiceOrderResource($this->completeServiceOrderDiagnosis->execute(
            $serviceOrder,
            new DateTimeImmutable,
        ));
    }

    public function cancel(int $serviceOrder): ServiceOrderResource
    {
        return new ServiceOrderResource($this->cancelServiceOrder->execute($serviceOrder, new DateTimeImmutable));
    }

    public function addAdditionalRepairs(
        AddAdditionalRepairsRequest $request,
        int $serviceOrder,
    ): ServiceOrderResource {
        return new ServiceOrderResource($this->addAdditionalRepairs->execute(
            $serviceOrder,
            array_map(
                fn (array $service): RequestedServiceData => new RequestedServiceData(
                    $service['service_id'],
                    $service['quantity'],
                ),
                $request->validated('services'),
            ),
        ));
    }
}
