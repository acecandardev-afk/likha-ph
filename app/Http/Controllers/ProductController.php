<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display product listing.
     */
    public function index(Request $request)
    {
        $query = Product::public()
            ->with(['artisan.artisanProfile', 'category', 'primaryImage']);

        // Filter by category (case-insensitive: "a" and "A" match the same)
        if ($request->filled('category')) {
            $categorySlug = strtolower(trim($request->category));
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->whereRaw('LOWER(slug) = ?', [$categorySlug]);
            });
        }

        // Search (case-insensitive: "a" and "A" match the same in name and description)
        if ($request->filled('search')) {
            $keyword = mb_strtolower(trim($request->search));
            $pattern = '%' . addcslashes($keyword, '%_\\') . '%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw('LOWER(name) LIKE ?', [$pattern])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$pattern]);
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);

        $categories = Category::active()->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show product details.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load([
            'artisan.artisanProfile',
            'category',
            'images',
            'approvedReviews.customer'
        ]);

        $relatedProducts = Product::public()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('images')
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}