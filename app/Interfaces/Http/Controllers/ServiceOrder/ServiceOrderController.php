<?php

namespace App\Interfaces\Http\Controllers\ServiceOrder;

use App\Application\ServiceOrder\AddAdditionalRepairs;
use App\Application\ServiceOrder\CancelServiceOrder;
use App\Application\ServiceOrder\CompleteServiceOrderDiagnosis;
use App\Application\ServiceOrder\CreateServiceOrder;
use App\Application\ServiceOrder\Data\RequestedInventoryItemCollection;
use App\Application\ServiceOrder\Data\RequestedInventoryItemData;
use App\Application\ServiceOrder\Data\RequestedServiceCollection;
use App\Application\ServiceOrder\Data\RequestedServiceData;
use App\Application\ServiceOrder\DeliverServiceOrder;
use App\Application\ServiceOrder\FinalizeServiceOrder;
use App\Application\ServiceOrder\GetServiceOrder;
use App\Application\ServiceOrder\GetServiceOrderExecutionTimeMetrics;
use App\Application\ServiceOrder\GetServiceOrderStatus;
use App\Application\ServiceOrder\ListServiceOrders;
use App\Application\ServiceOrder\StartServiceOrderDiagnosis;
use App\Interfaces\Http\Pagination\LengthAwarePaginatorFactory;
use App\Interfaces\Http\Requests\ServiceOrder\AddAdditionalRepairsRequest;
use App\Interfaces\Http\Requests\ServiceOrder\ServiceOrderExecutionTimeRequest;
use App\Interfaces\Http\Requests\ServiceOrder\StoreServiceOrderRequest;
use App\Interfaces\Http\Resources\ServiceOrderResource;
use App\Interfaces\Http\Resources\ServiceOrderStatusResource;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceOrderController
{
    public function __construct(
        private readonly AddAdditionalRepairs $addAdditionalRepairs,
        private readonly CancelServiceOrder $cancelServiceOrder,
        private readonly CompleteServiceOrderDiagnosis $completeServiceOrderDiagnosis,
        private readonly CreateServiceOrder $createServiceOrder,
        private readonly DeliverServiceOrder $deliverServiceOrder,
        private readonly FinalizeServiceOrder $finalizeServiceOrder,
        private readonly GetServiceOrder $getServiceOrder,
        private readonly GetServiceOrderExecutionTimeMetrics $getServiceOrderExecutionTimeMetrics,
        private readonly GetServiceOrderStatus $getServiceOrderStatus,
        private readonly ListServiceOrders $listServiceOrders,
        private readonly StartServiceOrderDiagnosis $startServiceOrderDiagnosis,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return ServiceOrderResource::collection(LengthAwarePaginatorFactory::make(
            $this->listServiceOrders->execute($perPage),
            $request,
        ));
    }

    public function executionTime(ServiceOrderExecutionTimeRequest $request): JsonResponse
    {
        $deliveredFrom = $request->validated('delivered_from');
        $deliveredTo = $request->validated('delivered_to');

        $metrics = $this->getServiceOrderExecutionTimeMetrics->execute(
            $deliveredFrom === null ? null : new DateTimeImmutable($deliveredFrom),
            $deliveredTo === null ? null : new DateTimeImmutable($deliveredTo),
            $request->validated('service_id'),
        );

        return response()->json(['data' => [
            'eligible_orders' => $metrics->eligibleOrders,
            'average_total_seconds' => $metrics->averageTotalSeconds,
            'average_seconds_by_status' => $metrics->averageSecondsByStatus,
        ]]);
    }

    public function store(StoreServiceOrderRequest $request): JsonResponse
    {
        $order = $this->createServiceOrder->execute(
            $request->string('customer_document')->toString(),
            $request->integer('vehicle_id'),
            new RequestedServiceCollection(...array_map(
                fn (array $service): RequestedServiceData => new RequestedServiceData(
                    $service['service_id'],
                    $service['quantity'],
                ),
                $request->validated('services'),
            )),
            new RequestedInventoryItemCollection(...array_map(
                fn (array $inventoryItem): RequestedInventoryItemData => new RequestedInventoryItemData(
                    $inventoryItem['inventory_item_id'],
                    $inventoryItem['quantity'],
                ),
                $request->validated('inventory_items'),
            )),
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

    public function show(int $serviceOrder): ServiceOrderResource
    {
        return new ServiceOrderResource($this->getServiceOrder->execute($serviceOrder));
    }

    public function status(int $serviceOrder): ServiceOrderStatusResource
    {
        return new ServiceOrderStatusResource($this->getServiceOrderStatus->execute($serviceOrder));
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

    public function finalize(int $serviceOrder): ServiceOrderResource
    {
        return new ServiceOrderResource($this->finalizeServiceOrder->execute(
            $serviceOrder,
            new DateTimeImmutable,
        ));
    }

    public function deliver(int $serviceOrder): ServiceOrderResource
    {
        return new ServiceOrderResource($this->deliverServiceOrder->execute(
            $serviceOrder,
            new DateTimeImmutable,
        ));
    }

    public function addAdditionalRepairs(
        AddAdditionalRepairsRequest $request,
        int $serviceOrder,
    ): ServiceOrderResource {
        return new ServiceOrderResource($this->addAdditionalRepairs->execute(
            $serviceOrder,
            new RequestedServiceCollection(...array_map(
                fn (array $service): RequestedServiceData => new RequestedServiceData(
                    $service['service_id'],
                    $service['quantity'],
                ),
                $request->validated('services'),
            )),
        ));
    }
}
