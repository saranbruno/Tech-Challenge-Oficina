<?php

use App\Interfaces\Http\Controllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('refresh', [AdminAuthController::class, 'refresh'])->name('refresh');
    Route::get('me', [AdminAuthController::class, 'me'])->middleware('auth:api')->name('me');
});
