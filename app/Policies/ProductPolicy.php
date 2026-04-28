<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can browse approved products
        return true;
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(?User $user, Product $product): bool
    {
        // Public can view approved products
        if ($product->isApproved() && $product->is_active) {
            return true;
        }

        // Artisan can view their own products regardless of status
        if ($user && $user->isArtisan() && $product->artisan_id === $user->id) {
            return true;
        }

        // Admin can view all products
        if ($user && $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->isArtisan() && ! $user->isSuspended();
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        // Only the artisan who owns the product can update it
        return $user->isArtisan()
            && $product->artisan_id === $user->id
            && ! $user->isSuspended();
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        // Artisan can delete their own products
        if ($user->isArtisan() && $product->artisan_id === $user->id) {
            return true;
        }

        // Admin can delete any product
        return $user->isAdmin();
    }

    /**
     * Determine if the user can approve/reject products.
     */
    public function approve(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can manage product images.
     */
    public function manageImages(User $user, Product $product): bool
    {
        return $user->isArtisan()
            && $product->artisan_id === $user->id
            && ! $user->isSuspended();
    }
}
