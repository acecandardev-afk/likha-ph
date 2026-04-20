<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

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
     * Add a friendly success message after login.
     */
    protected function authenticated(Request $request, $user)
    {
        return redirect()->intended($this->redirectPath())->with(
            'success',
            'Welcome back, ' . $user->name . '! You are now signed in.'
        );
    }

    /**
     * Keep failed login feedback generic and safe.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => ['We could not sign you in. Please check your email and password and try again.'],
        ]);
    }

    /**
     * Add a friendly message on logout.
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('home')->with('success', 'You have been signed out successfully.');
    }
}
