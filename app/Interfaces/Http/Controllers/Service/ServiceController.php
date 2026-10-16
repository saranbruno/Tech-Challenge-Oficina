<?php

namespace App\Interfaces\Http\Controllers\Service;

use App\Application\Service\CreateService;
use App\Application\Service\DeleteService;
use App\Application\Service\GetService;
use App\Application\Service\ListServices;
use App\Application\Service\UpdateService;
use App\Interfaces\Http\Pagination\LengthAwarePaginatorFactory;
use App\Interfaces\Http\Requests\Service\StoreServiceRequest;
use App\Interfaces\Http\Requests\Service\UpdateServiceRequest;
use App\Interfaces\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ServiceController
{
    public function index(Request $request, ListServices $listServices): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return ServiceResource::collection(LengthAwarePaginatorFactory::make(
            $listServices->execute($perPage),
            $request,
        ));
    }

    public function store(StoreServiceRequest $request, CreateService $createService): JsonResponse
    {
        $service = $createService->execute(...$this->parameters($request));

        return (new ServiceResource($service))->response()->setStatusCode(201);
    }

    public function show(int $service, GetService $getService): ServiceResource
    {
        return new ServiceResource($getService->execute($service));
    }

    public function update(UpdateServiceRequest $request, int $service, UpdateService $updateService): ServiceResource
    {
        return new ServiceResource($updateService->execute($service, ...$this->parameters($request)));
    }

    public function destroy(int $service, DeleteService $deleteService): Response
    {
        $deleteService->execute($service);

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
