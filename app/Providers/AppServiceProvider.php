<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ImageUploadService;
use App\Services\AddressService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Services\StockService;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register as singletons
        $this->app->singleton(ImageUploadService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(NotificationService::class);
        
        // Register with dependencies
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService($app->make(StockService::class));
        });

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(StockService::class),
                $app->make(CartService::class),
                $app->make(NotificationService::class)
            );
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(ImageUploadService::class),
                $app->make(NotificationService::class)
            );
        });

        $this->app->singleton(AddressService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(
            [
                'auth.apply-artisan',
                'auth.register-artisan',
                'account.edit',
            ],
            function ($view) {
                $view->with('phAddressBootstrap', app(AddressService::class)->getClientBootstrap());
            }
        );
    }
}