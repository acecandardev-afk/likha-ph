<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRider
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isRider()) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->isSuspended()) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['email' => 'Your rider account is currently suspended.']);
        }

        return $next($request);
    }
}
