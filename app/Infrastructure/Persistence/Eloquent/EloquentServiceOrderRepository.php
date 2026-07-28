<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Service\ValueObjects\UnitPrice;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderInventoryItem;
use App\Domain\ServiceOrder\ServiceOrderService;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderInventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderServiceModel;
use Illuminate\Support\Facades\DB;

class EloquentServiceOrderRepository implements ServiceOrderRepository
{
    public function create(ServiceOrder $serviceOrder): ServiceOrder
    {
        return DB::transaction(function () use ($serviceOrder): ServiceOrder {
            $model = new ServiceOrderModel;
            $this->fill($model, $serviceOrder)->save();

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

            return $this->toDomain($model);
        });
    }

    public function findOrFail(int $id): ServiceOrder
    {
        return $this->toDomain(ServiceOrderModel::query()->findOrFail($id));
    }

    public function update(ServiceOrder $serviceOrder): ServiceOrder
    {
        $model = ServiceOrderModel::query()->findOrFail($serviceOrder->id);
        $this->fill($model, $serviceOrder)->save();

        return $this->toDomain($model);
    }

    private function fill(ServiceOrderModel $model, ServiceOrder $serviceOrder): ServiceOrderModel
    {
        return $model->fill([
            'customer_id' => $serviceOrder->customerId,
            'vehicle_id' => $serviceOrder->vehicleId,
            'status' => $serviceOrder->status->value,
            'total_amount' => $serviceOrder->totalAmount(),
            'received_at' => $serviceOrder->receivedAt,
            'diagnosis_started_at' => $serviceOrder->diagnosisStartedAt,
            'awaiting_approval_at' => $serviceOrder->awaitingApprovalAt,
            'execution_started_at' => $serviceOrder->executionStartedAt,
            'finalized_at' => $serviceOrder->finalizedAt,
            'delivered_at' => $serviceOrder->deliveredAt,
            'cancelled_at' => $serviceOrder->cancelledAt,
        ]);
    }

    private function toDomain(ServiceOrderModel $model): ServiceOrder
    {
        $services = ServiceOrderServiceModel::query()
            ->where('service_order_id', $model->getKey())
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceOrderServiceModel $service) => new ServiceOrderService(
                $service->service_id,
                $service->quantity,
                new UnitPrice($service->unit_price_snapshot),
            ))
            ->all();

        $inventoryItems = ServiceOrderInventoryItemModel::query()
            ->where('service_order_id', $model->getKey())
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceOrderInventoryItemModel $inventoryItem) => new ServiceOrderInventoryItem(
                $inventoryItem->inventory_item_id,
                InventoryItemType::from($inventoryItem->type_snapshot),
                $inventoryItem->quantity,
                new UnitPrice($inventoryItem->unit_price_snapshot),
            ))
            ->all();

        return ServiceOrder::reconstitute(
            $model->getKey(),
            $model->customer_id,
            $model->vehicle_id,
            ServiceOrderStatus::from($model->status),
            $model->received_at,
            $model->diagnosis_started_at,
            $model->awaiting_approval_at,
            $model->execution_started_at,
            $model->finalized_at,
            $model->delivered_at,
            $model->cancelled_at,
            $services,
            $inventoryItems,
        );
    }
}
