<?php

use App\Interfaces\Http\Controllers\Auth\AdminAuthController;
use App\Interfaces\Http\Controllers\Customer\CustomerController;
use App\Interfaces\Http\Controllers\Vehicle\VehicleController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('refresh', [AdminAuthController::class, 'refresh'])->name('refresh');
    Route::get('me', [AdminAuthController::class, 'me'])->middleware('auth:api')->name('me');
});

Route::middleware('auth:api')->group(function (): void {
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});
