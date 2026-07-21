<?php

namespace App\Providers;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Customer\Contracts\CustomerRepository;
use App\Application\Service\Contracts\ServiceRepository;
use App\Application\Vehicle\Contracts\VehicleRepository;
use App\Infrastructure\Auth\JwtAdminTokenProvider;
use App\Infrastructure\Persistence\Eloquent\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentServiceRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentVehicleRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminTokenProvider::class, JwtAdminTokenProvider::class);
        $this->app->bind(CustomerRepository::class, EloquentCustomerRepository::class);
        $this->app->bind(ServiceRepository::class, EloquentServiceRepository::class);
        $this->app->bind(VehicleRepository::class, EloquentVehicleRepository::class);
    }

    public function boot(): void {}
}
