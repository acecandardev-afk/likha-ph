<?php

namespace App\Http\Controllers;

use App\Models\User;

class ArtisanProfileController extends Controller
{
    /**
     * Display all artisans.
     */
    public function index()
    {
        $artisans = User::artisans()
            ->active()
            ->with('artisanProfile')
            ->withCount(['products' => function ($query) {
                $query->public();
            }])
            ->has('artisanProfile')
            ->paginate(12);

        return view('artisans.index', compact('artisans'));
    }

    /**
     * Show artisan profile and products.
     */
    public function show(User $artisan)
    {
        if (! $artisan->isArtisan()) {
            abort(404);
        }

        $artisan->load('artisanProfile');

        $viewer = auth()->user();
        $products = $artisan->products()
            ->public()
            ->visibleToShopper($viewer)
            ->with('images', 'category')
            ->latest()
            ->paginate(12);

        $isOwnProfile = $viewer && (int) $viewer->id === (int) $artisan->id;

        return view('artisans.show', compact('artisan', 'products', 'isOwnProfile'));
    }
}
