<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\ServiceOrder\Contracts\ServiceOrderMetricsQuery;
use App\Domain\ServiceOrder\Enums\ServiceOrderStatus;
use App\Domain\ServiceOrder\ServiceOrder;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceOrderModel;
use DateTimeImmutable;

final readonly class EloquentServiceOrderMetricsQuery implements ServiceOrderMetricsQuery
{
    public function __construct(private ServiceOrderMapper $mapper) {}

    public function completedForMetrics(
        ?DateTimeImmutable $deliveredFrom,
        ?DateTimeImmutable $deliveredTo,
        ?int $serviceId,
    ): array {
        return ServiceOrderModel::query()
            ->where('status', ServiceOrderStatus::Delivered->value)
            ->when($deliveredFrom, fn ($query) => $query->where('delivered_at', '>=', $deliveredFrom))
            ->when($deliveredTo, fn ($query) => $query->where('delivered_at', '<', $deliveredTo->modify('+1 day')))
            ->when($serviceId, fn ($query) => $query->whereExists(
                fn ($services) => $services
                    ->selectRaw('1')
                    ->from('service_order_services')
                    ->whereColumn('service_order_services.service_order_id', 'service_orders.id')
                    ->where('service_order_services.service_id', $serviceId),
            ))
            ->orderBy('id')
            ->get()
            ->map(fn (ServiceOrderModel $model): ServiceOrder => $this->mapper->toDomain($model))
            ->all();
    }
}
