<?php

namespace App\Interfaces\Http\Controllers\Customer;

use App\Application\Customer\CreateCustomer;
use App\Application\Customer\DeleteCustomer;
use App\Application\Customer\GetCustomer;
use App\Application\Customer\ListCustomers;
use App\Application\Customer\UpdateCustomer;
use App\Interfaces\Http\Pagination\LengthAwarePaginatorFactory;
use App\Interfaces\Http\Requests\Customer\StoreCustomerRequest;
use App\Interfaces\Http\Requests\Customer\UpdateCustomerRequest;
use App\Interfaces\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerController
{
    public function index(Request $request, ListCustomers $listCustomers): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return CustomerResource::collection(LengthAwarePaginatorFactory::make(
            $listCustomers->execute($perPage),
            $request,
        ));
    }

    public function store(StoreCustomerRequest $request, CreateCustomer $createCustomer): JsonResponse
    {
        $customer = $createCustomer->execute(
            $request->string('name')->toString(),
            $request->string('document')->toString(),
            $request->validated('email'),
            $request->validated('phone'),
        );

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    public function show(int $customer, GetCustomer $getCustomer): CustomerResource
    {
        return new CustomerResource($getCustomer->execute($customer));
    }

    public function update(UpdateCustomerRequest $request, int $customer, UpdateCustomer $updateCustomer): CustomerResource
    {
        $updated = $updateCustomer->execute(
            $customer,
            $request->string('name')->toString(),
            $request->string('document')->toString(),
            $request->validated('email'),
            $request->validated('phone'),
        );

        return new CustomerResource($updated);
    }

    public function destroy(int $customer, DeleteCustomer $deleteCustomer): Response
    {
        $deleteCustomer->execute($customer);

        return response()->noContent();
    }
}
