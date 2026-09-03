<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderStatusQuery;
use App\Application\ServiceOrder\Data\ServiceOrderStatusData;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;
use DateTimeImmutable;

final readonly class EloquentServiceOrderStatusQuery implements ServiceOrderStatusQuery
{
    public function findOrFail(int $serviceOrderId): ServiceOrderStatusData
    {
        $model = ServiceOrderModel::query()
            ->select(['id', 'status', 'received_at', 'diagnosis_started_at', 'awaiting_approval_at', 'execution_started_at', 'finalized_at', 'delivered_at', 'cancelled_at'])
            ->find($serviceOrderId);

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $this->toData($model);
    }

    public function findForClientOrFail(string $customerDocument, string $trackingTokenHash): ServiceOrderStatusData
    {
        $model = ServiceOrderModel::query()
            ->join('customers', 'customers.id', '=', 'service_orders.customer_id')
            ->where('customers.document', $customerDocument)
            ->where('service_orders.tracking_token_hash', $trackingTokenHash)
            ->select(['service_orders.id', 'service_orders.status', 'service_orders.received_at', 'service_orders.diagnosis_started_at', 'service_orders.awaiting_approval_at', 'service_orders.execution_started_at', 'service_orders.finalized_at', 'service_orders.delivered_at', 'service_orders.cancelled_at'])
            ->first();

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $this->toData($model);
    }

    private function toData(ServiceOrderModel $model): ServiceOrderStatusData
    {
        $status = ServiceOrderStatus::from($model->status);

        $lastTransitionAt = match ($status) {
            ServiceOrderStatus::Received => $model->received_at,
            ServiceOrderStatus::InDiagnosis => $model->diagnosis_started_at,
            ServiceOrderStatus::AwaitingApproval => $model->awaiting_approval_at,
            ServiceOrderStatus::InExecution => $model->execution_started_at,
            ServiceOrderStatus::Finalized => $model->finalized_at,
            ServiceOrderStatus::Delivered => $model->delivered_at,
            ServiceOrderStatus::Cancelled => $model->cancelled_at,
        };

        return new ServiceOrderStatusData($model->getKey(), $status, new DateTimeImmutable($lastTransitionAt->format(DATE_ATOM)));
    }
}
