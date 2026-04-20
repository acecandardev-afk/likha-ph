<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Order;
use App\Models\ArtisanProfile;
use App\Models\Review;
use App\Models\Message;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ArtisanProfilePolicy;
use App\Policies\ReviewPolicy;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        ArtisanProfile::class => ArtisanProfilePolicy::class,
        Review::class => ReviewPolicy::class,
        Message::class => MessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}