<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageUploadService;
use App\Services\NotificationService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends ArtisanController
{
    public function __construct(
        protected ImageUploadService $imageUploadService,
        protected StockService $stockService,
        protected NotificationService $notificationService,
    ) {
        parent::__construct();
    }

    /**
     * Display artisan's products.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $artisan = $this->getArtisan();
        $artisan->load('artisanProfile');
        $shopName = $artisan->artisanProfile?->workshop_name;

        $query = $artisan->products()->with('category', 'images', 'primaryImage');

        // Filter by approval status
        if ($request->has('status')) {
            $query->where('approval_status', $request->status);
        }

        $products = $query->latest()->paginate(20);

        return view('artisan.products.index', compact('products', 'shopName'));
    }

    /**
     * Show form to create new product.
     */
    public function create()
    {
        $categories = Category::active()->get();

        return view('artisan.products.create', compact('categories'));
    }

    /**
     * Store a new product.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'artisan_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'approval_status' => 'pending',
                'is_active' => true,
            ]);

            // Upload images
            foreach ($request->file('images') as $index => $imageFile) {
                $path = $this->imageUploadService->uploadProductImage($imageFile, $product->id);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()
            ->route('artisan.products.index')
            ->with('success', 'Product created successfully and submitted for approval.');
    }

    /**
     * Show product details.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load('category', 'images', 'approvals.reviewer');

        return view('artisan.products.show', compact('product'));
    }

    /**
     * Show form to edit product.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::active()->get();
        $product->load('images');

        return view('artisan.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();
        $oldStock = $product->stock;

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
            ]);

            // Remove images
            if ($request->has('remove_images')) {
                $imagesToRemove = ProductImage::whereIn('id', $request->remove_images)
                    ->where('product_id', $product->id)
                    ->get();

                foreach ($imagesToRemove as $image) {
                    Storage::disk('products')->delete($image->image_path);
                    $image->delete();
                }
            }

            // Add new images
            if ($request->hasFile('new_images')) {
                $currentMaxSort = $product->images()->max('sort_order') ?? -1;

                foreach ($request->file('new_images') as $index => $imageFile) {
                    $path = $this->imageUploadService->uploadProductImage($imageFile, $product->id);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => $product->images()->count() === 0 && $index === 0,
                        'sort_order' => $currentMaxSort + $index + 1,
                    ]);
                }
            }
        });

        $product->refresh();

        if ($product->approval_status === 'approved') {
            if ($product->stock === 0 && $oldStock > 0) {
                $this->notificationService->notifyOutOfStock($product);
            } elseif (
                $product->stock > 0
                && $product->stock <= StockService::LOW_STOCK_THRESHOLD
                && $oldStock > StockService::LOW_STOCK_THRESHOLD
            ) {
                $this->notificationService->notifyLowStock($product);
            }
        }

        return redirect()
            ->route('artisan.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Check if product has orders
        if ($product->orderItems()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete product with existing orders.']);
        }

        DB::transaction(function () use ($product) {
            // Delete images
            foreach ($product->images as $image) {
                Storage::disk('products')->delete($image->image_path);
                $image->delete();
            }

            $product->delete();
        });

        return redirect()
            ->route('artisan.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
