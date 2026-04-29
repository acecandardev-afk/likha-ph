<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use App\Models\UserNotification;
use Closure;
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
        $payload = [
            'uiCartCount' => 0,
            'uiUnreadNotificationsCount' => 0,
            'uiApplicationBanner' => null,
            'uiGoogleSignInAvailable' => filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret')),
        ];

        try {
            try {
                $root = $request->getSchemeAndHttpHost().rtrim($request->getBasePath(), '/');
                if ($root !== '') {
                    URL::useOrigin($root);
                }
            } catch (\Throwable $e) {
                report($e);
            }

            $userId = $request->user()?->id;

            if ($userId) {
                try {
                    if (! $request->user()->isRider()) {
                        $payload['uiCartCount'] = Cache::remember("ui:cartCount:{$userId}", now()->addSeconds(5), function () use ($userId) {
                            return (int) Cart::where('user_id', $userId)->sum('quantity');
                        });
                    }

                    $payload['uiUnreadNotificationsCount'] = Cache::remember("ui:unreadNotificationsCount:{$userId}", now()->addSeconds(5), function () use ($userId) {
                        return (int) UserNotification::where('user_id', $userId)->where('is_read', false)->count();
                    });

                    $payload['uiApplicationBanner'] = Cache::remember("ui:applicationBanner:{$userId}", now()->addSeconds(10), function () use ($userId) {
                        return UserNotification::where('user_id', $userId)
                            ->where('is_read', false)
                            ->whereIn('type', ['artisan_application_approved', 'artisan_application_rejected'])
                            ->latest()
                            ->first();
                    });
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            View::share($payload);
        } catch (\Throwable $e) {
            report($e);
            View::share($payload);
        }

        return $next($request);
    }
}
