<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

class ReviewPolicy
{
    /**
     * Determine if the user can view any reviews.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view approved reviews
        return true;
    }

    /**
     * Determine if the user can view the review.
     */
    public function view(?User $user, Review $review): bool
    {
        // Anyone can view approved reviews
        if ($review->is_approved) {
            return true;
        }

        // Customer can view their own reviews
        if ($user && $user->isCustomer() && $review->customer_id === $user->id) {
            return true;
        }

        // Admin can view all reviews
        return $user && $user->isAdmin();
    }

    /**
     * Determine if the user can create a review.
     */
    public function create(User $user, Order $order, Product $product): bool
    {
        // Must be a customer
        if (!$user->isCustomer() || $user->isSuspended()) {
            return false;
        }

        // Order must belong to the customer
        if ($order->customer_id !== $user->id) {
            return false;
        }

        // Order must be completed
        if (!$order->isCompleted()) {
            return false;
        }

        // Product must be in the order
        $productInOrder = $order->items()->where('product_id', $product->id)->exists();
        if (!$productInOrder) {
            return false;
        }

        // Customer hasn't already reviewed this product for this order
        $existingReview = Review::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->where('customer_id', $user->id)
            ->exists();

        return !$existingReview;
    }

    /**
     * Determine if the user can update the review.
     */
    public function update(User $user, Review $review): bool
    {
        // Customer can update their own review within 7 days
        return $user->isCustomer() 
            && $review->customer_id === $user->id 
            && $review->created_at->diffInDays(now()) <= 7
            && !$user->isSuspended();
    }

    /**
     * Determine if the user can delete the review.
     */
    public function delete(User $user, Review $review): bool
    {
        // Customer can delete their own review
        if ($user->isCustomer() && $review->customer_id === $user->id) {
            return true;
        }

        // Admin can delete any review
        return $user->isAdmin();
    }

    /**
     * Determine if the user can approve/reject reviews.
     */
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}