<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Service\Contracts\ServiceRepository;
use App\Domain\Service\Service;
use App\Domain\Service\ValueObjects\UnitPrice;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceModel;

class EloquentServiceRepository implements ServiceRepository
{
    public function paginate(int $perPage): mixed
    {
        return ServiceModel::query()->orderBy('id')->paginate($perPage);
    }

    public function findOrFail(int $id): Service
    {
        return $this->toDomain(ServiceModel::query()->findOrFail($id));
    }

    public function save(Service $service): Service
    {
        $model = $service->id === null
            ? new ServiceModel
            : ServiceModel::query()->findOrFail($service->id);

        $model->fill([
            'name' => $service->name,
            'unit_price' => $service->unitPrice->cents,
        ])->save();

        return $this->toDomain($model);
    }

    public function delete(int $id): void
    {
        ServiceModel::query()->findOrFail($id)->delete();
    }

    private function toDomain(ServiceModel $model): Service
    {
        return new Service(
            $model->getKey(),
            $model->name,
            new UnitPrice($model->unit_price),
        );
    }
}
