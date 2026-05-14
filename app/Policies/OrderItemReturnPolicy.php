<?php

namespace App\Policies;

use App\Models\OrderItemReturn;
use App\Models\User;

class OrderItemReturnPolicy
{
    public function view(User $user, OrderItemReturn $return): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $return->customer_id === (int) $user->id) {
            return true;
        }

        return $user->isArtisan() && (int) $return->artisan_id === (int) $user->id;
    }

    public function approve(User $user, OrderItemReturn $return): bool
    {
        return $user->isAdmin() && ! $user->isSuspended();
    }

    public function reject(User $user, OrderItemReturn $return): bool
    {
        return $this->approve($user, $return);
    }
}
