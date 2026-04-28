<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cart / checkout flows should be available to any authenticated,
        // non-suspended user. We only block completely unauthenticated users.
        if (! $request->user()) {
            return redirect()
                ->route('login', ['intended' => '/customer/cart'])
                ->with('error', 'Please log in to access your cart.');
        }

        if ($request->user()->isRider()) {
            return redirect()
                ->route('rider.dashboard')
                ->with('error', 'Rider accounts cannot place orders or use the cart. Switch to a customer account to shop.');
        }

        return $next($request);
    }
}
