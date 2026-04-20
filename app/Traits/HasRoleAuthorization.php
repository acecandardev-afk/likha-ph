<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;

trait HasRoleAuthorization
{
    /**
     * Ensure user has admin role.
     */
    protected function ensureIsAdmin(): void
    {
        if (!auth()->user()?->isAdmin()) {
            throw new AuthorizationException('Admin access required.');
        }
    }

    /**
     * Ensure user has artisan role.
     */
    protected function ensureIsArtisan(): void
    {
        if (!auth()->user()?->isArtisan()) {
            throw new AuthorizationException('Artisan access required.');
        }
    }

    /**
     * Ensure user has customer role.
     */
    protected function ensureIsCustomer(): void
    {
        if (!auth()->user()?->isCustomer()) {
            throw new AuthorizationException('Customer access required.');
        }
    }

    /**
     * Ensure user account is active.
     */
    protected function ensureIsActive(): void
    {
        if (auth()->user()?->isSuspended()) {
            throw new AuthorizationException('Account is suspended.');
        }
    }
}