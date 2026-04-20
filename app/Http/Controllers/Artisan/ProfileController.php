<?php

namespace App\Http\Controllers\Artisan;

use App\Models\ArtisanProfile;
use App\Support\Guihulngan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends ArtisanController
{
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
            $artisansDir = storage_path('app/public/artisans');
            if (!File::isDirectory($artisansDir)) {
                File::makeDirectory($artisansDir, 0755, true);
            }

            $filename = uniqid('artisan_' . $artisan->id . '_') . '.jpg';

            $image = Image::read($request->file('profile_image'));
            $image->scale(width: 400);
            $image->toJpeg(quality: 85)->save(
                $artisansDir . '/' . $filename
            );

            // Delete old image
            if ($profile && $profile->profile_image) {
                Storage::disk('artisans')->delete($profile->profile_image);
            }

            $validated['profile_image'] = $filename;
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

        $profile->update(['profile_image' => null]);

        return back()->with('success', 'Profile picture removed successfully.');
    }
}