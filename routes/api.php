<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/api/admin.php'));

Route::prefix('client')
    ->name('client.')
    ->group(base_path('routes/api/client.php'));
