<?php

namespace App\Interfaces\Http\Controllers\ServiceOrder;

use App\Application\ServiceOrder\CreateServiceOrder;
use App\Application\ServiceOrder\Data\RequestedInventoryItemData;
use App\Application\ServiceOrder\Data\RequestedServiceData;
use App\Interfaces\Http\Requests\ServiceOrder\StoreServiceOrderRequest;
use App\Interfaces\Http\Resources\ServiceOrderResource;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;

class ServiceOrderController
{
    public function __construct(private readonly CreateServiceOrder $createServiceOrder) {}

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
}
