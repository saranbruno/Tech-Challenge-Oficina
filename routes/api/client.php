<?php

use App\Interfaces\Http\Controllers\ServiceOrder\ClientServiceOrderController;
use Illuminate\Support\Facades\Route;

Route::post('service-orders/tracking', [ClientServiceOrderController::class, 'show'])->name('service-orders.tracking.show');
Route::post('service-orders/approve', [ClientServiceOrderController::class, 'approve'])->name('service-orders.approve');
