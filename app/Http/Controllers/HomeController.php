<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display homepage.
     * Admins are redirected to the admin dashboard.
     */
    public function index(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $featuredProducts = Product::public()
            ->visibleToShopper($request->user())
            ->with(['artisan.artisanProfile', 'category', 'images', 'primaryImage'])
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::active()
            ->withCount(['products' => function ($query) {
                $query->public();
            }])
            ->get();

        $featuredArtisans = User::artisans()
            ->active()
            ->with('artisanProfile')
            ->withCount(['products' => function ($query) {
                $query->public();
            }])
            ->has('artisanProfile')
            ->take(4)
            ->get();

        $stats = [
            'products' => Product::public()->count(),
            'artisans' => User::artisans()->active()->has('artisanProfile')->count(),
            'categories' => Category::active()->count(),
        ];

        $heroProduct = $featuredProducts->first();
        $heroImageUrl = $heroProduct?->primaryImage?->image_url
            ?? $heroProduct?->images?->first()?->image_url
            ?? null;

        return view('home', compact('featuredProducts', 'categories', 'featuredArtisans', 'stats', 'heroImageUrl'));
    }
}
