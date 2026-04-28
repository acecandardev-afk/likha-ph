<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine if the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        $order = $message->order;

        return ($user->isCustomer() && $order->customer_id === $user->id)
            || ($user->isArtisan() && $order->artisan_id === $user->id)
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create a message for this order.
     */
    public function create(User $user, Order $order): bool
    {
        return (($user->isCustomer() && $order->customer_id === $user->id)
            || ($user->isArtisan() && $order->artisan_id === $user->id))
            && ! $user->isSuspended();
    }

    /**
     * Determine if the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        // User can delete their own messages within 5 minutes
        if ($message->sender_id === $user->id
            && $message->created_at->diffInMinutes(now()) <= 5) {
            return true;
        }

        // Admin can delete any message
        return $user->isAdmin();
    }
}
