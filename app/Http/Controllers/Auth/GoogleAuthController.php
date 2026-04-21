<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->with('error', 'Google sign-in is not configured for this site.');
        }

        return Socialite::driver('google')
            ->redirectUrl($this->googleRedirectUri($request))
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->googleRedirectUri($request))
                ->user();
        } catch (Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in failed. Please try again or use email and password.']);
        }

        $email = $googleUser->getEmail();
        if (! is_string($email) || $email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your Google account did not provide an email address.']);
        }

        $googleId = (string) $googleUser->getId();
        $name = $googleUser->getName() ?: (explode('@', $email)[0] ?? 'User');
        $avatar = $googleUser->getAvatar();

        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if ($user->isSuspended()) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'This account is suspended.']);
            }

            $user->forceFill([
                'google_id' => $googleId,
                'avatar_url' => $avatar ?: $user->avatar_url,
                'name' => $name ?: $user->name,
            ])->save();
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar_url' => $avatar,
                'password' => Hash::make(Str::password(64)),
                'role' => 'customer',
                'status' => 'active',
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended(route('home'))->with(
            'success',
            'Welcome, '.$user->name.'! You are signed in with Google.'
        );
    }

    /**
     * Must match a URI listed under Google Cloud Console → OAuth client → Authorized redirect URIs.
     * Using the incoming request root fixes mismatches vs APP_URL (port, host, or subpath, e.g. XAMPP).
     */
    private function googleRedirectUri(Request $request): string
    {
        $explicit = config('services.google.redirect_uri_explicit');
        if (is_string($explicit) && $explicit !== '') {
            return rtrim($explicit, '/');
        }

        return rtrim($request->root(), '/').'/auth/google/callback';
    }
}
