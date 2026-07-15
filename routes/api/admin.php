<?php

use App\Interfaces\Http\Controllers\Auth\AdminAuthController;
use App\Interfaces\Http\Controllers\Customer\CustomerController;
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
});
