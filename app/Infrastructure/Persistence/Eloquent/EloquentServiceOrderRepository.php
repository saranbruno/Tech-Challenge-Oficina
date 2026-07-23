<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;

class EloquentServiceOrderRepository implements ServiceOrderRepository
{
    public function create(ServiceOrder $serviceOrder): ServiceOrder
    {
        $model = new ServiceOrderModel;
        $this->fill($model, $serviceOrder)->save();

        return $this->toDomain($model);
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
        );
    }
}
