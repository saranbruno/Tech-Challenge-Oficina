<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Shared\Data\PaginatedResult;
use App\Application\Shared\Exceptions\ResourceNotFound;
use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Domain\Vehicle\ValueObjects\LicensePlate;
use App\Domain\Vehicle\Vehicle;
use App\Infrastructure\Persistence\Eloquent\Models\VehicleModel;

class EloquentVehicleRepository implements VehicleRepository
{
    public function paginate(int $perPage): PaginatedResult
    {
        return PaginatedResultFactory::make(
            VehicleModel::query()->orderBy('id')->paginate($perPage),
            fn (VehicleModel $model): Vehicle => $this->toDomain($model),
        );
    }

    public function findOrFail(int $id): Vehicle
    {
        return $this->toDomain($this->findModel($id));
    }

    public function licensePlateExists(string $licensePlate, ?int $exceptId = null): bool
    {
        return VehicleModel::query()
            ->where('license_plate', $licensePlate)
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function save(Vehicle $vehicle): Vehicle
    {
        $model = $vehicle->id === null
            ? new VehicleModel
            : $this->findModel($vehicle->id);

        $model->fill([
            'customer_id' => $vehicle->customerId,
            'license_plate' => $vehicle->licensePlate->value,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
        ])->save();

        return $this->toDomain($model);
    }

    public function delete(int $id): void
    {
        $this->findModel($id)->delete();
    }

    private function findModel(int $id): VehicleModel
    {
        $model = VehicleModel::query()->find($id);

        if ($model === null) {
            throw new ResourceNotFound;
        }

        return $model;
    }

    private function toDomain(VehicleModel $model): Vehicle
    {
        return new Vehicle(
            $model->getKey(),
            $model->customer_id,
            new LicensePlate($model->license_plate),
            $model->brand,
            $model->model,
            $model->year,
        );
    }
}
