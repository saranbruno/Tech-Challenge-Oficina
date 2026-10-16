<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderApproval;
use App\Application\ServiceOrder\Contracts\ServiceOrderBudgetDecision;
use App\Application\ServiceOrder\Data\BudgetDecisionResult;
use App\Application\ServiceOrder\Enums\BudgetDecision;
use App\Application\ServiceOrder\Exceptions\InsufficientInventoryStock;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Infrastructure\Persistence\Eloquent\Models\InventoryItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class EloquentServiceOrderApproval implements ServiceOrderApproval, ServiceOrderBudgetDecision
{
    public function __construct(private ServiceOrderMapper $mapper) {}

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
                ->first();

            if ($model === null) {
                throw new ResourceNotFound;
            }

            return $this->approveLockedOrder($model, $occurredAt);
        });
    }

    public function process(
        int $serviceOrderId,
        BudgetDecision $decision,
        DateTimeImmutable $occurredAt,
    ): BudgetDecisionResult {
        return DB::transaction(function () use ($serviceOrderId, $decision, $occurredAt): BudgetDecisionResult {
            $model = ServiceOrderModel::query()
                ->whereKey($serviceOrderId)
                ->lockForUpdate()
                ->first();

            if ($model === null) {
                throw new ResourceNotFound;
            }

            $serviceOrder = $this->mapper->toDomain($model);

            if ($decision === BudgetDecision::Approved && $serviceOrder->status === ServiceOrderStatus::InExecution) {
                return new BudgetDecisionResult($serviceOrder, false);
            }

            if ($decision === BudgetDecision::Rejected && $serviceOrder->status === ServiceOrderStatus::Cancelled) {
                return new BudgetDecisionResult($serviceOrder, false);
            }

            $updatedServiceOrder = match ($decision) {
                BudgetDecision::Approved => $this->approveLockedOrder($model, $occurredAt),
                BudgetDecision::Rejected => $this->cancelLockedOrder($model, $occurredAt),
            };

            return new BudgetDecisionResult($updatedServiceOrder, true);
        });
    }

    private function approveLockedOrder(ServiceOrderModel $model, DateTimeImmutable $occurredAt): ServiceOrder
    {
        $serviceOrder = $this->mapper->toDomain($model);
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

        $this->mapper->fill($model, $serviceOrder)->save();

        return $this->mapper->toDomain($model);
    }

    private function cancelLockedOrder(ServiceOrderModel $model, DateTimeImmutable $occurredAt): ServiceOrder
    {
        $serviceOrder = $this->mapper->toDomain($model);
        $serviceOrder->cancel($occurredAt);

        $this->mapper->fill($model, $serviceOrder)->save();

        return $this->mapper->toDomain($model);
    }
}
