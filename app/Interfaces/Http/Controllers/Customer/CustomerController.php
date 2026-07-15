<?php

namespace App\Interfaces\Http\Controllers\Customer;

use App\Application\Customer\CustomerService;
use App\Interfaces\Http\Requests\Customer\StoreCustomerRequest;
use App\Interfaces\Http\Requests\Customer\UpdateCustomerRequest;
use App\Interfaces\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerController
{
    public function __construct(private readonly CustomerService $customers) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return CustomerResource::collection($this->customers->list($perPage));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->string('name')->toString(), $request->string('document')->toString());

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    public function show(int $customer): CustomerResource
    {
        return new CustomerResource($this->customers->find($customer));
    }

    public function update(UpdateCustomerRequest $request, int $customer): CustomerResource
    {
        $updated = $this->customers->update($customer, $request->string('name')->toString(), $request->string('document')->toString());

        return new CustomerResource($updated);
    }

    public function destroy(int $customer): Response
    {
        $this->customers->delete($customer);

        return response()->noContent();
    }
}
