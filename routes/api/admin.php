<?php

use App\Interfaces\Http\Controllers\Auth\AdminAuthController;
use App\Interfaces\Http\Controllers\Customer\CustomerController;
use App\Interfaces\Http\Controllers\Inventory\InventoryItemController;
use App\Interfaces\Http\Controllers\Service\ServiceController;
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
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::get('inventory-items', [InventoryItemController::class, 'index'])->name('inventory-items.index');
    Route::post('inventory-items', [InventoryItemController::class, 'store'])->name('inventory-items.store');
    Route::get('inventory-items/{inventoryItem}', [InventoryItemController::class, 'show'])->name('inventory-items.show');
    Route::put('inventory-items/{inventoryItem}', [InventoryItemController::class, 'update'])->name('inventory-items.update');
    Route::put('inventory-items/{inventoryItem}/stock', [InventoryItemController::class, 'adjustStock'])->name('inventory-items.stock.update');
    Route::get('inventory-items/{inventoryItem}/movements', [InventoryItemController::class, 'movements'])->name('inventory-items.movements.index');
    Route::delete('inventory-items/{inventoryItem}', [InventoryItemController::class, 'destroy'])->name('inventory-items.destroy');
});
