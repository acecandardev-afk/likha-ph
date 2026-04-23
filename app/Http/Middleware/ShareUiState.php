<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Cart;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareUiState
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $root = $request->getSchemeAndHttpHost().rtrim($request->getBasePath(), '/');
        if ($root !== '') {
            URL::forceRootUrl($root);
        }

        $userId = $request->user()?->id;

        $cartCount = 0;
        $unreadNotificationsCount = 0;
        $applicationBanner = null;

        if ($userId) {
            $cartCount = Cache::remember("ui:cartCount:{$userId}", now()->addSeconds(5), function () use ($userId) {
                return (int) Cart::where('user_id', $userId)->sum('quantity');
            });

            $unreadNotificationsCount = Cache::remember("ui:unreadNotificationsCount:{$userId}", now()->addSeconds(5), function () use ($userId) {
                return (int) UserNotification::where('user_id', $userId)->where('is_read', false)->count();
            });

            // Only look for the artisan application banner if needed.
            $applicationBanner = Cache::remember("ui:applicationBanner:{$userId}", now()->addSeconds(10), function () use ($userId) {
                return UserNotification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->whereIn('type', ['artisan_application_approved', 'artisan_application_rejected'])
                    ->latest()
                    ->first();
            });
        }

        // Server-side only: never expose credentials to the client; views only get a boolean.
        $googleSignInAvailable = filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));

        View::share([
            'uiCartCount' => $cartCount,
            'uiUnreadNotificationsCount' => $unreadNotificationsCount,
            'uiApplicationBanner' => $applicationBanner,
            'uiGoogleSignInAvailable' => $googleSignInAvailable,
        ]);

        return $next($request);
    }
}
