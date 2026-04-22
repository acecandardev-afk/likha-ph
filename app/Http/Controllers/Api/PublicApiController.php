<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    public function products(Request $request)
    {
        return Product::query()
            ->public()
            ->with(['category:id,name,slug', 'artisan:id,name'])
            ->select(['id', 'artisan_id', 'category_id', 'name', 'description', 'price', 'stock', 'created_at'])
            ->paginate($request->integer('per_page', 15));
    }

    public function showProduct(Product $product)
    {
        abort_unless($product->isApproved(), 404);

        $product->load([
            'category:id,name,slug',
            'artisan:id,name',
            'images:id,product_id,image_path,is_primary',
            'approvedReviews:id,product_id,customer_id,rating,comment,created_at',
            'approvedReviews.customer:id,name',
        ]);

        return $product;
    }

    public function categories()
    {
        return Category::query()
            ->active()
            ->withCount('products')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'icon']);
    }

    public function artisans(Request $request)
    {
        return User::query()
            ->artisans()
            ->active()
            ->with('artisanProfile:user_id,workshop_name,city,barangay,story,profile_image')
            ->withCount(['products' => fn ($q) => $q->public()])
            ->paginate($request->integer('per_page', 15), ['id', 'name']);
    }

    public function showArtisan(User $artisan)
    {
        abort_unless($artisan->isArtisan(), 404);

        $artisan->load([
            'artisanProfile:user_id,workshop_name,story,city,barangay,profile_image',
            'products' => fn ($q) => $q->public()->latest()->limit(24),
            'products.images:id,product_id,image_path,is_primary',
        ]);

        $artisan->loadCount(['products' => fn ($q) => $q->public()]);

        return $artisan;
    }

    public function regions()
    {
        return cache()->remember('regions', 3600, function () {
            return Region::select('id', 'name', 'code')->orderBy('name')->get();
        });
    }

    public function provinces(Region $region)
    {
        return cache()->remember("provinces.{$region->id}", 3600, function () use ($region) {
            return $region->provinces()->select('id', 'name', 'code')->orderBy('name')->get();
        });
    }

    public function cities(Province $province)
    {
        return cache()->remember("cities.{$province->id}", 3600, function () use ($province) {
            return $province->cities()->select('id', 'name', 'code')->orderBy('name')->get();
        });
    }

    public function barangays(City $city)
    {
        return cache()->remember("barangays.{$city->id}", 3600, function () use ($city) {
            return $city->barangays()->select('id', 'name', 'code')->orderBy('name')->get();
        });
    }
}
