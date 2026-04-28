<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsArtisan
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isArtisan()) {
            abort(403, 'Access denied. Artisan account required.');
        }

        // Artisan accounts go through an application/review step.
        // Users with status != active are redirected to the waiting page.
        if (($request->user()->status ?? null) !== 'active') {
            return redirect()->route('artisan.apply.pending');
        }

        return $next($request);
    }
}
