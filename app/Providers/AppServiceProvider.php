<?php

namespace App\Providers;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Inventory\Contracts\InventoryItemRepository;
use App\Application\Service\Contracts\ServiceRepository;
use App\Application\ServiceOrder\Contracts\ServiceOrderRepository;
use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Infrastructure\Auth\JwtAdminTokenProvider;
use App\Infrastructure\Persistence\Eloquent\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentInventoryItemRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentServiceOrderRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentServiceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentVehicleRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminTokenProvider::class, JwtAdminTokenProvider::class);
        $this->app->bind(CustomerRepository::class, EloquentCustomerRepository::class);
        $this->app->bind(InventoryItemRepository::class, EloquentInventoryItemRepository::class);
        $this->app->bind(ServiceRepository::class, EloquentServiceRepository::class);
        $this->app->bind(ServiceOrderRepository::class, EloquentServiceOrderRepository::class);
        $this->app->bind(VehicleRepository::class, EloquentVehicleRepository::class);
    }

    public function boot(): void {}
}
