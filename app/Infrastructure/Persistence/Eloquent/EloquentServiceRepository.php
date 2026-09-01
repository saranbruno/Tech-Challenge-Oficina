<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Service\Contracts\ServiceRepository;
use App\Application\Shared\Data\PaginatedResult;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Domain\Service\Service;
use App\Domain\Service\ValueObjects\UnitPrice;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceModel;

class EloquentServiceRepository implements ServiceRepository
{
    public function paginate(int $perPage): PaginatedResult
    {
        return PaginatedResultFactory::make(
            ServiceModel::query()->orderBy('id')->paginate($perPage),
            fn (ServiceModel $model): Service => $this->toDomain($model),
        );
    }

    public function findOrFail(int $id): Service
    {
        return $this->toDomain($this->findModel($id));
    }

    public function save(Service $service): Service
    {
        $model = $service->id === null
            ? new ServiceModel
            : $this->findModel($service->id);

        $model->fill([
            'name' => $service->name,
            'unit_price' => $service->unitPrice->cents,
        ])->save();

        return $this->toDomain($model);
    }

    public function delete(int $id): void
    {
        $this->findModel($id)->delete();
    }

    private function findModel(int $id): ServiceModel
    {
        $model = ServiceModel::query()->find($id);

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $model;
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
