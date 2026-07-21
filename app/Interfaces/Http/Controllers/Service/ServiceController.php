<?php

namespace App\Interfaces\Http\Controllers\Service;

use App\Application\Service\ServiceService;
use App\Interfaces\Http\Requests\Service\StoreServiceRequest;
use App\Interfaces\Http\Requests\Service\UpdateServiceRequest;
use App\Interfaces\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ServiceController
{
    public function __construct(private readonly ServiceService $services) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return ServiceResource::collection($this->services->list($perPage));
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->services->create(...$this->parameters($request));

        return (new ServiceResource($service))->response()->setStatusCode(201);
    }

    public function show(int $service): ServiceResource
    {
        return new ServiceResource($this->services->find($service));
    }

    public function update(UpdateServiceRequest $request, int $service): ServiceResource
    {
        return new ServiceResource($this->services->update($service, ...$this->parameters($request)));
    }

    public function destroy(int $service): Response
    {
        $this->services->delete($service);

        return response()->noContent();
    }

    private function parameters(StoreServiceRequest $request): array
    {
        return [
            $request->string('name')->toString(),
            $request->integer('unit_price'),
        ];
    }
}
