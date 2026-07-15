<?php

namespace App\Providers;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Infrastructure\Auth\JwtAdminTokenProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminTokenProvider::class, JwtAdminTokenProvider::class);
    }

    public function boot(): void {}
}
