<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderInventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderServiceModel;
use Illuminate\Support\Facades\DB;

final readonly class EloquentServiceOrderRepository implements ServiceOrderRepository
{
    public function __construct(private ServiceOrderMapper $mapper) {}

    public function create(ServiceOrder $serviceOrder): ServiceOrder
    {
        return DB::transaction(function () use ($serviceOrder): ServiceOrder {
            $model = new ServiceOrderModel;
            $this->mapper->fill($model, $serviceOrder)->save();

            foreach ($serviceOrder->services() as $service) {
                ServiceOrderServiceModel::query()->create([
                    'service_order_id' => $model->getKey(),
                    'service_id' => $service->serviceId,
                    'quantity' => $service->quantity,
                    'unit_price_snapshot' => $service->unitPriceSnapshot->cents,
                ]);
            }

            foreach ($serviceOrder->inventoryItems() as $inventoryItem) {
                ServiceOrderInventoryItemModel::query()->create([
                    'service_order_id' => $model->getKey(),
                    'inventory_item_id' => $inventoryItem->inventoryItemId,
                    'type_snapshot' => $inventoryItem->typeSnapshot->value,
                    'quantity' => $inventoryItem->quantity,
                    'unit_price_snapshot' => $inventoryItem->unitPriceSnapshot->cents,
                ]);
            }

            return $this->mapper->toDomain($model, $serviceOrder->trackingToken);
        });
    }

    public function findOrFail(int $id): ServiceOrder
    {
        return $this->mapper->toDomain($this->findModel($id));
    }

    public function update(ServiceOrder $serviceOrder): ServiceOrder
    {
        $model = DB::transaction(function () use ($serviceOrder): ServiceOrderModel {
            $model = $this->findModel($serviceOrder->id);
            $this->mapper->fill($model, $serviceOrder)->save();

            foreach ($serviceOrder->services() as $service) {
                ServiceOrderServiceModel::query()->updateOrCreate(
                    [
                        'service_order_id' => $model->getKey(),
                        'service_id' => $service->serviceId,
                    ],
                    [
                        'quantity' => $service->quantity,
                        'unit_price_snapshot' => $service->unitPriceSnapshot->cents,
                    ],
                );
            }

            return $model;
        });

        return $this->mapper->toDomain($model);
    }

    private function findModel(int $id): ServiceOrderModel
    {
        $model = ServiceOrderModel::query()->find($id);

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $model;
    }
}
