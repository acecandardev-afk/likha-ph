<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their orders
        return true;
    }

    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Buyer (customer_id) can view their own orders (works for customers and artisans who placed orders)
        if ($order->customer_id === $user->id) {
            return true;
        }

        // Artisan can view orders they need to fulfill (orders placed with them)
        if ($user->isArtisan() && $order->artisan_id === $user->id) {
            return true;
        }

        // Admin can view all orders
        return $user->isAdmin();
    }

    /**
     * Determine if the user can create orders (place an order as buyer).
     * Any authenticated, non-suspended user can checkout (cart/checkout allow all roles).
     */
    public function create(User $user): bool
    {
        return !$user->isSuspended();
    }

    /**
     * Determine if the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only artisan can update order status (for fulfillment)
        return $user->isArtisan() 
            && $order->artisan_id === $user->id 
            && !$user->isSuspended();
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Buyer: only while still pending (see Order::canBeCancelled)
        if ($order->customer_id === $user->id) {
            return $order->canBeCancelled();
        }

        // Admin: support cancellations in any state
        return $user->isAdmin();
    }

    /**
     * Determine if the user can mark order as completed.
     */
    public function complete(User $user, Order $order): bool
    {
        // Artisan can mark their orders as completed
        if ($user->isArtisan() && $order->artisan_id === $user->id) {
            return $order->canBeCompleted();
        }

        // Admin can complete any order
        return $user->isAdmin();
    }

    /**
     * Determine if the user can approve the order.
     */
    public function approve(User $user, Order $order): bool
    {
        if ($user->isArtisan() && $order->artisan_id === $user->id) {
            return $order->canBeApproved();
        }

        return $user->isAdmin();
    }

    /**
     * Determine if the user can view order messages.
     */
    public function viewMessages(User $user, Order $order): bool
    {
        return $order->customer_id === $user->id
            || ($user->isArtisan() && $order->artisan_id === $user->id)
            || $user->isAdmin();
    }

    /**
     * Determine if the user can send messages for this order.
     */
    public function sendMessage(User $user, Order $order): bool
    {
        return ($order->customer_id === $user->id
            || ($user->isArtisan() && $order->artisan_id === $user->id))
            && !$user->isSuspended();
    }
}