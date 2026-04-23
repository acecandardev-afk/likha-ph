<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ArtisanProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use App\Support\Guihulngan;
use App\Support\SignupEmailValidation;

class ArtisanRegistrationController extends Controller
{
    /**
     * Show the artisan registration form.
     */
    public function create()
    {
        return view('auth.register-artisan');
    }

    /**
     * Register a new artisan (user + artisan profile).
     */
    public function store(Request $request)
    {
        Log::info('artisan.register_guest_store.hit', [
            'auth_check' => auth()->check(),
            'email' => $request->input('email'),
            'workshop_name' => $request->input('workshop_name'),
            'barangay' => $request->input('barangay'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => SignupEmailValidation::registrationEmailRules(),
            'password' => ['required', 'confirmed', Password::defaults()],
            'workshop_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'barangay' => Guihulngan::barangayRules(true),
            'id_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ], [
            'email.unique' => 'This email address is already registered.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'artisan',
            'status' => 'pending',
            'phone' => $validated['phone'] ?? null,
        ]);

        $idPath = $request->file('id_photo')->storePublicly('artisan-ids', 'public');

        ArtisanProfile::create([
            'user_id' => $user->id,
            'workshop_name' => $validated['workshop_name'],
            'city' => config('guihulngan.city_name'),
            'barangay' => $validated['barangay'] ?? null,
            'id_photo' => $idPath,
        ]);

        Auth::login($user);

        return redirect()
            ->route('artisan.apply.pending')
            ->with('success', 'Your artisan application has been submitted. Please wait for our email.');
    }

    /**
     * Apply as an artisan (upgrade an existing account).
     * Used by logged-in customers so we do NOT create a new User record.
     */
    public function apply()
    {
        return view('auth.apply-artisan');
    }

    /**
     * Save artisan application for the authenticated user.
     */
    public function applyStore(Request $request)
    {
        $user = $request->user();

        Log::info('artisan.apply_store.hit', [
            'user_id' => $user?->id,
            'is_artisan' => $user?->isArtisan(),
            'status' => $user?->status ?? null,
            'workshop_name' => $request->input('workshop_name'),
            'barangay' => $request->input('barangay'),
        ]);

        if ($user->isArtisan()) {
            if (($user->status ?? null) !== 'active') {
                return redirect()
                    ->route('artisan.apply.pending')
                    ->with('success', 'Your request is already being reviewed. Please wait for our email.');
            }

            return redirect()
                ->route('artisan.dashboard')
                ->with('success', 'You are already an artisan. Redirecting to your dashboard.');
        }

        $validated = $request->validate([
            'workshop_name' => ['required', 'string', 'max:255'],
            'barangay' => Guihulngan::barangayRules(true),
            'id_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ], [
            'workshop_name.required' => 'Please enter your workshop / business name.',
        ]);

        $user->update([
            'role' => 'artisan',
            'status' => 'pending',
        ]);

        $existingProfile = ArtisanProfile::where('user_id', $user->id)->first();
        if ($existingProfile?->id_photo) {
            Storage::disk('public')->delete($existingProfile->id_photo);
        }
        $idPath = $request->file('id_photo')->storePublicly('artisan-ids', 'public');

        ArtisanProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'workshop_name' => $validated['workshop_name'],
                'city' => config('guihulngan.city_name'),
                'barangay' => $validated['barangay'] ?? null,
                'id_photo' => $idPath,
            ]
        );

        return redirect()
            ->route('artisan.apply.pending')
            ->with('success', 'Your request is now being reviewed. Please wait for our email.');
    }

    /**
     * Waiting page after applying as an artisan.
     */
    public function pending()
    {
        $user = request()->user();

        if (!$user || !$user->isArtisan()) {
            return redirect()->route('home');
        }

        return view('auth.artisan-apply-pending');
    }

    /**
     * Dispatch artisan registration vs application based on auth state.
     * - Guest: renders the full artisan sign-up form.
     * - Authenticated customer: renders the shorter apply form.
     */
    public function createOrApply()
    {
        if (auth()->check()) {
            return $this->apply();
        }

        return $this->create();
    }

    /**
     * Dispatch artisan registration vs application store.
     */
    public function storeOrApply(Request $request)
    {
        Log::info('artisan.storeOrApply.hit', [
            'auth_check' => auth()->check(),
            'route' => $request->path(),
        ]);

        if (auth()->check()) {
            return $this->applyStore($request);
        }

        // Guest submits "register as artisan" but the email might already exist.
        // In that case, we redirect to login so the user can use the
        // authenticated "apply as artisan" flow (upgrade current account).
        $email = $request->input('email');
        if ($email) {
            $existing = User::where('email', $email)->first();
            if ($existing) {
                return redirect()
                    ->route('login', ['intended' => route('register.artisan')])
                    ->with('error', 'This email is already registered. Please log in, then apply as an artisan.');
            }
        }

        return $this->store($request);
    }
}
