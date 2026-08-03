<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/docs', 'swagger')->name('docs.swagger');
Route::get('/docs/openapi.yaml', fn () => response()->file(
    base_path('docs/openapi.yaml'),
    ['Content-Type' => 'application/yaml'],
))->name('docs.openapi');
