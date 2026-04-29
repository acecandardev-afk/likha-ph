<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if ($request->user() && $request->user()->isSuspended()) {
                try {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                } catch (\Throwable $e) {
                    report($e);
                    Auth::logout();
                }

                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $next($request);
    }
}
