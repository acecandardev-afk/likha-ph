<?php

namespace App\Policies;

use App\Models\ArtisanProfile;
use App\Models\User;

class ArtisanProfilePolicy
{
    /**
     * Determine if the user can view any artisan profiles.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can browse artisan profiles
        return true;
    }

    /**
     * Determine if the user can view the artisan profile.
     */
    public function view(?User $user, ArtisanProfile $artisanProfile): bool
    {
        // Anyone can view artisan profiles
        return true;
    }

    /**
     * Determine if the user can create artisan profiles.
     */
    public function create(User $user): bool
    {
        // Only artisans without existing profile can create
        return $user->isArtisan() && ! $user->artisanProfile;
    }

    /**
     * Determine if the user can update the artisan profile.
     */
    public function update(User $user, ArtisanProfile $artisanProfile): bool
    {
        // Only the artisan who owns the profile can update it
        return $user->isArtisan()
            && $artisanProfile->user_id === $user->id
            && ! $user->isSuspended();
    }

    /**
     * Determine if the user can delete the artisan profile.
     */
    public function delete(User $user, ArtisanProfile $artisanProfile): bool
    {
        // Admin can delete any profile
        return $user->isAdmin();
    }
}
