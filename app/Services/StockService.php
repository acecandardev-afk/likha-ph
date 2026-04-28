<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockService
{
    public const LOW_STOCK_THRESHOLD = 5;

    /**
     * Check if product has sufficient stock.
     */
    public function hasStock(Product $product, int $quantity): bool
    {
        return $product->stock >= $quantity;
    }

    /**
     * Reserve stock for cart items (soft reservation).
     */
    public function reserveStock(Product $product, int $quantity): bool
    {
        if (! $this->hasStock($product, $quantity)) {
            return false;
        }

        // In a more advanced system, you might create a stock_reservations table
        // For now, we just validate availability
        return true;
    }

    /**
     * Decrement stock when order is placed.
     */
    public function decrementStock(Product $product, int $quantity): bool
    {
        if (! $this->hasStock($product, $quantity)) {
            throw new \Exception("Insufficient stock for product: {$product->name}");
        }

        return $product->decrement('stock', $quantity);
    }

    /**
     * Increment stock when order is cancelled.
     */
    public function incrementStock(Product $product, int $quantity): bool
    {
        return $product->increment('stock', $quantity);
    }

    /**
     * Restore stock from order items.
     */
    public function restoreStockFromOrder($orderItems): void
    {
        foreach ($orderItems as $item) {
            $this->incrementStock($item->product, $item->quantity);
        }
    }

    /**
     * Get low stock products for an artisan.
     */
    public function getLowStockProducts(int $artisanId, int $threshold = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('artisan_id', $artisanId)
            ->approved()
            ->where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->with('category')
            ->get();
    }

    /**
     * Get out of stock products for an artisan.
     */
    public function getOutOfStockProducts(int $artisanId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('artisan_id', $artisanId)
            ->approved()
            ->where('stock', 0)
            ->with('category')
            ->get();
    }

    /**
     * Update product stock.
     */
    public function updateStock(Product $product, int $newStock): bool
    {
        if ($newStock < 0) {
            throw new \Exception('Stock cannot be negative.');
        }

        return $product->update(['stock' => $newStock]);
    }

    /**
     * Bulk update stock for multiple products.
     */
    public function bulkUpdateStock(array $updates): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        DB::transaction(function () use ($updates, &$results) {
            foreach ($updates as $update) {
                try {
                    $product = Product::findOrFail($update['product_id']);
                    $this->updateStock($product, $update['stock']);
                    $results['success'][] = $product->id;
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'product_id' => $update['product_id'],
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Check stock availability for multiple items.
     */
    public function checkMultipleStock(array $items): array
    {
        $unavailable = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product || ! $this->hasStock($product, $item['quantity'])) {
                $unavailable[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name ?? 'Unknown',
                    'requested' => $item['quantity'],
                    'available' => $product->stock ?? 0,
                ];
            }
        }

        return $unavailable;
    }
}
