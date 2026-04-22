<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicApiController;

Route::controller(PublicApiController::class)->group(function () {
    Route::get('/products', 'products');
    Route::get('/products/{product}', 'showProduct');
    Route::get('/categories', 'categories');
    Route::get('/artisans', 'artisans');
    Route::get('/artisans/{artisan}', 'showArtisan')->whereNumber('artisan');
    Route::get('/regions', 'regions');
    Route::get('/provinces/{region}', 'provinces');
    Route::get('/cities/{province}', 'cities');
    Route::get('/barangays/{city}', 'barangays');
});
