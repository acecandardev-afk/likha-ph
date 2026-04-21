<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the login form. Store intended URL from query so redirect after login works.
     */
    public function showLoginForm(Request $request)
    {
        $intended = $request->query('intended');
        if (is_string($intended) && $intended !== '' && (str_starts_with($intended, '/') || str_starts_with($intended, config('app.url', '')))) {
            session()->put('url.intended', $intended);
        }

        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        $email = strtolower(trim($validated['email']));
        $password = $validated['password'];

        // Case-insensitive email match (PostgreSQL string compare is case-sensitive).
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user || ! Hash::check($password, $user->getAuthPassword())) {
            return back()
                ->withErrors(['email' => 'We could not sign you in. Please check your email and password and try again.'])
                ->onlyInput('email');
        }

        if ($user->isSuspended()) {
            return back()
                ->withErrors(['email' => 'Your account has been suspended. Please contact support.'])
                ->onlyInput('email');
        }

        Auth::login($user, $remember);

        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Welcome back!');
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out successfully.');
    }
}
