<?php

namespace App\Providers;

use App\Services\AddressService;
use App\Services\CartService;
use App\Services\DeliveryService;
use App\Services\ImageUploadService;
use App\Services\LedgerPostingService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\StockService;
use App\Services\VoucherService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LedgerPostingService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(NotificationService::class);

        // Register with dependencies
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService($app->make(StockService::class));
        });

        $this->app->singleton(VoucherService::class);

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(StockService::class),
                $app->make(CartService::class),
                $app->make(NotificationService::class),
                $app->make(DeliveryService::class),
                $app->make(VoucherService::class)
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
                'customer.checkout.index',
            ],
            function ($view) {
                $view->with('phAddressBootstrap', app(AddressService::class)->getClientBootstrap());
            }
        );

        View::composer('layouts.admin.nav', function ($view) {
            // One DB round-trip (same filters as Product::pending(), Payment::pending(), etc.)
            $t = DB::getTablePrefix();
            $row = DB::selectOne(
                "SELECT
                    (SELECT COUNT(*) FROM {$t}products WHERE approval_status = ?) AS products,
                    (SELECT COUNT(*) FROM {$t}payments WHERE verification_status = ?) AS payments,
                    (SELECT COUNT(*) FROM {$t}users WHERE role = ? AND status = ?) AS artisans,
                    (SELECT COUNT(*) FROM {$t}order_packages WHERE delivery_status = ?) AS deliveries,
                    (SELECT COUNT(*) FROM {$t}delivery_reports WHERE status = 'open') AS reports",
                [
                    'pending',
                    'pending',
                    'artisan',
                    'pending',
                    DeliveryService::STATUS_PENDING_ASSIGNMENT,
                ]
            );

            $view->with('adminPendingCounts', [
                'products' => (int) ($row->products ?? 0),
                'payments' => (int) ($row->payments ?? 0),
                'artisans' => (int) ($row->artisans ?? 0),
                'deliveries' => (int) ($row->deliveries ?? 0),
                'reports' => (int) ($row->reports ?? 0),
            ]);
        });
    }
}
