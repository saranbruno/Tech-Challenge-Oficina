<?php

namespace App\Interfaces\Http\Controllers\Vehicle;

use App\Application\Vehicle\VehicleService;
use App\Interfaces\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Interfaces\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Interfaces\Http\Resources\VehicleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class VehicleController
{
    public function __construct(private readonly VehicleService $vehicles) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return VehicleResource::collection($this->vehicles->list($perPage));
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicles->create(...$this->parameters($request));

        return (new VehicleResource($vehicle))->response()->setStatusCode(201);
    }

    public function show(int $vehicle): VehicleResource
    {
        return new VehicleResource($this->vehicles->find($vehicle));
    }

    public function update(UpdateVehicleRequest $request, int $vehicle): VehicleResource
    {
        return new VehicleResource($this->vehicles->update($vehicle, ...$this->parameters($request)));
    }

    public function destroy(int $vehicle): Response
    {
        $this->vehicles->delete($vehicle);

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
