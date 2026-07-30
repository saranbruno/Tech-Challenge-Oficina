<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\ServiceOrder\Exceptions\InsufficientInventoryStock;
use App\Domain\Inventory\Enums\InventoryItemType;
use App\Domain\Service\ValueObjects\UnitPrice;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Domain\ServiceOrder\ServiceOrderInventoryItem;
use App\Domain\ServiceOrder\ServiceOrderService;
use App\Infrastructure\Persistence\Eloquent\Models\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderInventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentServiceOrderRepository implements ServiceOrderRepository
{
    public function paginate(int $perPage): mixed
    {
        $paginator = ServiceOrderModel::query()->orderByDesc('id')->paginate($perPage);
        $paginator->setCollection($paginator->getCollection()->map(
            fn (ServiceOrderModel $model): ServiceOrder => $this->toDomain($model),
        ));

        return $paginator;
    }

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

            return $this->toDomain($model, $serviceOrder->trackingToken);
        });
    }

    public function findOrFail(int $id): ServiceOrder
    {
        return $this->toDomain(ServiceOrderModel::query()->findOrFail($id));
    }

    public function findForClientOrFail(string $customerDocument, string $trackingTokenHash): ServiceOrder
    {
        $model = ServiceOrderModel::query()
            ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
            ->where('customers.document', $customerDocument)
            ->where('service_orders.tracking_token_hash', $trackingTokenHash)
            ->select('service_orders.*')
            ->firstOrFail();

        return $this->toDomain($model);
    }

    public function update(ServiceOrder $serviceOrder): ServiceOrder
    {
        $model = DB::transaction(function () use ($serviceOrder): ServiceOrderModel {
            $model = ServiceOrderModel::query()->findOrFail($serviceOrder->id);
            $this->fill($model, $serviceOrder)->save();

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

        return $this->toDomain($model);
    }

    public function approveForClient(
        string $customerDocument,
        string $trackingTokenHash,
        DateTimeImmutable $occurredAt,
    ): ServiceOrder {
        return DB::transaction(function () use ($customerDocument, $trackingTokenHash, $occurredAt): ServiceOrder {
            $model = ServiceOrderModel::query()
                ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
                ->where('customers.document', $customerDocument)
                ->where('service_orders.tracking_token_hash', $trackingTokenHash)
                ->select('service_orders.*')
                ->lockForUpdate()
                ->firstOrFail();
            $serviceOrder = $this->toDomain($model);
            $serviceOrder->approveBudget($occurredAt);

            $requestedItems = collect($serviceOrder->inventoryItems())->sortBy('inventoryItemId');
            $inventoryModels = InventoryItemModel::query()
                ->whereIn('id', $requestedItems->pluck('inventoryItemId'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($requestedItems as $requestedItem) {
                $inventoryModel = $inventoryModels->get($requestedItem->inventoryItemId);

                if ($inventoryModel === null || $inventoryModel->quantity_available < $requestedItem->quantity) {
                    throw new InsufficientInventoryStock('Estoque insuficiente para iniciar a execucao da ordem de servico.');
                }
            }

            foreach ($requestedItems as $requestedItem) {
                $inventoryModel = $inventoryModels->get($requestedItem->inventoryItemId);
                $before = $inventoryModel->quantity_available;
                $after = $before - $requestedItem->quantity;
                $inventoryModel->quantity_available = $after;
                $inventoryModel->save();
                StockMovementModel::query()->create([
                    'inventory_item_id' => $inventoryModel->getKey(),
                    'admin_user_id' => null,
                    'service_order_id' => $model->getKey(),
                    'type' => 'service_order_consumption',
                    'quantity_change' => -$requestedItem->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                ]);
            }

            $this->fill($model, $serviceOrder)->save();

            return $this->toDomain($model);
        });
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
            'tracking_token_hash' => $serviceOrder->trackingTokenHash,
        ]);
    }

    private function toDomain(ServiceOrderModel $model, ?string $trackingToken = null): ServiceOrder
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
            $model->tracking_token_hash,
            $trackingToken,
        );
    }
}
