<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsArtisan;
use App\Http\Middleware\EnsureUserIsCustomer;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ShareUiState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->then(function () {
        // Configure health checks to exclude database check during startup
        Health::checks([
            CacheCheck::class,
            DebugModeCheck::class,
            EnvironmentCheck::class,
            OptimizedAppCheck::class,
            ScheduleCheck::class,
            UsedDiskSpaceCheck::class,
        ]);
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Render sits behind a reverse proxy. Trust forwarded headers so Laravel
        // correctly detects HTTPS and generates secure URLs (e.g. /logout).
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'artisan' => EnsureUserIsArtisan::class,
            'customer' => EnsureUserIsCustomer::class,
            'active' => EnsureUserIsActive::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsureUserIsActive::class,
            ShareUiState::class,
        ]);

        // When an unauthenticated user hits customer cart/checkout, send them to login with intended=/customer/cart
        // so that after login they are taken to the cart page instead of the previous (e.g. home) page.
        // When an already-logged-in user visits /login?intended=..., send them to that URL instead of home.
        $middleware->redirectTo(
            guests: function ($request) {
                $path = $request->path();
                if (str_starts_with($path, 'customer/cart') || str_starts_with($path, 'customer/checkout')) {
                    return Route::has('login') ? route('login', ['intended' => '/customer/cart']) : url('/login');
                }
                return Route::has('login') ? route('login') : url('/login');
            },
            users: function ($request) {
                $intended = $request->query('intended');
                if (is_string($intended) && $intended !== '' && (str_starts_with($intended, '/') || str_starts_with($intended, config('app.url', '')))) {
                    return $intended;
                }
                return Route::has('home') ? route('home') : '/';
            }
        );
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withSchedule(function ($schedule) {
        $schedule->command('orders:update-statuses')->everyMinute();
    })->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();