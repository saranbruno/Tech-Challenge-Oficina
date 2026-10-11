<?php

namespace App\Interfaces\Http\Controllers\Vehicle;

use App\Application\Vehicle\CreateVehicle;
use App\Application\Vehicle\DeleteVehicle;
use App\Application\Vehicle\GetVehicle;
use App\Application\Vehicle\ListVehicles;
use App\Application\Vehicle\UpdateVehicle;
use App\Interfaces\Http\Pagination\LengthAwarePaginatorFactory;
use App\Interfaces\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Interfaces\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Interfaces\Http\Resources\VehicleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class VehicleController
{
    public function index(Request $request, ListVehicles $listVehicles): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return VehicleResource::collection(LengthAwarePaginatorFactory::make(
            $listVehicles->execute($perPage),
            $request,
        ));
    }

    public function store(StoreVehicleRequest $request, CreateVehicle $createVehicle): JsonResponse
    {
        $vehicle = $createVehicle->execute(...$this->parameters($request));

        return (new VehicleResource($vehicle))->response()->setStatusCode(201);
    }

    public function show(int $vehicle, GetVehicle $getVehicle): VehicleResource
    {
        return new VehicleResource($getVehicle->execute($vehicle));
    }

    public function update(UpdateVehicleRequest $request, int $vehicle, UpdateVehicle $updateVehicle): VehicleResource
    {
        return new VehicleResource($updateVehicle->execute($vehicle, ...$this->parameters($request)));
    }

    public function destroy(int $vehicle, DeleteVehicle $deleteVehicle): Response
    {
        $deleteVehicle->execute($vehicle);

        return response()->noContent();
    }

    private function parameters(StoreVehicleRequest $request): array
    {
        return [
            $request->integer('customer_id'),
            $request->string('license_plate')->toString(),
            $request->string('brand')->toString(),
            $request->string('model')->toString(),
            $request->integer('year'),
        ];
    }
}
