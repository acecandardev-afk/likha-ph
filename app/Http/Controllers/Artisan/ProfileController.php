<?php

namespace App\Http\Controllers\Artisan;

use App\Models\ArtisanProfile;
use App\Services\ImageUploadService;
use App\Support\Guihulngan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends ArtisanController
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {
        parent::__construct();
    }

    /**
     * Show artisan profile edit form.
     */
    public function edit()
    {
        $artisan = $this->getArtisan();
        $profile = $artisan->artisanProfile;

        if (!$profile) {
            $profile = new ArtisanProfile(['user_id' => $artisan->id]);
        }

        return view('artisan.profile.edit', compact('profile'));
    }

    /**
     * Update artisan profile.
     */
    public function update(Request $request)
    {
        $artisan = $this->getArtisan();
        $profile = $artisan->artisanProfile;

        $validated = $request->validate([
            'workshop_name' => 'required|string|max:255',
            'story' => 'nullable|string|max:2000',
            'barangay' => Guihulngan::barangayRules(true),
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['city'] = config('guihulngan.city_name');

        // Update user info
        $artisan->update([
            'phone' => $validated['phone'],
            'address' => $validated['address'],
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($profile && $profile->profile_image) {
                $this->imageUploadService->deleteArtisanImage($profile->profile_image);
            }

            $validated['profile_image'] = $this->imageUploadService->uploadArtisanImage(
                $request->file('profile_image'),
                $artisan->id
            );
        }

        // Create or update profile
        if ($profile) {
            $this->authorize('update', $profile);
            $profile->update($validated);
        } else {
            $this->authorize('create', ArtisanProfile::class);
            ArtisanProfile::create(array_merge($validated, [
                'user_id' => $artisan->id,
            ]));
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove profile picture.
     */
    public function removeProfileImage()
    {
        $artisan = $this->getArtisan();
        $profile = $artisan->artisanProfile;

        if (!$profile || !$profile->profile_image) {
            return back()->with('error', 'No profile picture to remove.');
        }

        $this->authorize('update', $profile);

        if ($profile->profile_image) {
            $this->imageUploadService->deleteArtisanImage($profile->profile_image);
        }

        $profile->update(['profile_image' => null]);

        return back()->with('success', 'Profile picture removed successfully.');
    }
}