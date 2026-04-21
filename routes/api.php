<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

Route::get('/products', function (Request $request) {
    return Product::query()
        ->public()
        ->with(['category:id,name,slug', 'artisan:id,name'])
        ->select(['id', 'artisan_id', 'category_id', 'name', 'description', 'price', 'stock', 'created_at'])
        ->paginate($request->integer('per_page', 15));
});

Route::get('/products/{product}', function (Product $product) {
    abort_unless($product->isApproved(), 404);

    $product->load([
        'category:id,name,slug',
        'artisan:id,name',
        'images:id,product_id,image_path,is_primary',
        'approvedReviews:id,product_id,customer_id,rating,comment,created_at',
        'approvedReviews.customer:id,name',
    ]);

    return $product;
});

Route::get('/categories', function () {
    return Category::query()
        ->active()
        ->withCount('products')
        ->orderBy('name')
        ->get(['id', 'name', 'slug', 'description', 'icon']);
});

Route::get('/artisans', function (Request $request) {
    return User::query()
        ->artisans()
        ->active()
        ->with('artisanProfile:user_id,workshop_name,city,barangay,story,profile_image')
        ->withCount(['products' => fn ($q) => $q->public()])
        ->paginate($request->integer('per_page', 15), ['id', 'name']);
});

Route::get('/artisans/{artisan}', function (User $artisan) {
    abort_unless($artisan->isArtisan(), 404);

    $artisan->load([
        'artisanProfile:user_id,workshop_name,story,city,barangay,profile_image',
        'products' => fn ($q) => $q->public()->latest()->limit(24),
        'products.images:id,product_id,image_path,is_primary',
    ]);
    $artisan->loadCount(['products' => fn ($q) => $q->public()]);

    return $artisan;
})->whereNumber('artisan');

// Address API endpoints for cascading dropdowns
Route::get('/regions', function () {
    return Cache::remember('regions', 3600, function () {
        return \App\Models\Region::select('id', 'name', 'code')->orderBy('name')->get();
    });
});

Route::get('/provinces/{region}', function (\App\Models\Region $region) {
    return Cache::remember("provinces.{$region->id}", 3600, function () use ($region) {
        return $region->provinces()->select('id', 'name', 'code')->orderBy('name')->get();
    });
});

Route::get('/cities/{province}', function (\App\Models\Province $province) {
    return Cache::remember("cities.{$province->id}", 3600, function () use ($province) {
        return $province->cities()->select('id', 'name', 'code')->orderBy('name')->get();
    });
});

Route::get('/barangays/{city}', function (\App\Models\City $city) {
    return Cache::remember("barangays.{$city->id}", 3600, function () use ($city) {
        return $city->barangays()->select('id', 'name', 'code')->orderBy('name')->get();
    });
});
