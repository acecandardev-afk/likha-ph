<?php

if (! function_exists('format_currency')) {
    /**
     * Format amount as Philippine Peso
     */
    function format_currency($amount, $decimals = 2): string
    {
        return '₱'.number_format($amount, $decimals);
    }
}

if (! function_exists('format_date')) {
    /**
     * Format date in human-readable format
     */
    function format_date($date, $format = 'M d, Y'): string
    {
        return $date ? $date->format($format) : 'N/A';
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format datetime in human-readable format
     */
    function format_datetime($datetime, $format = 'M d, Y h:i A'): string
    {
        return $datetime ? $datetime->format($format) : 'N/A';
    }
}

if (! function_exists('cart_count')) {
    /**
     * Get current user's cart count
     */
    function cart_count(): int
    {
        if (! auth()->check() || ! auth()->user()->isCustomer()) {
            return 0;
        }

        return app(\App\Services\CartService::class)->getCartCount(auth()->user());
    }
}

if (! function_exists('order_status_color')) {
    /**
     * Get Bootstrap color class for order status
     */
    function order_status_color(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}

if (! function_exists('payment_status_color')) {
    /**
     * Get Bootstrap color class for payment status
     */
    function payment_status_color(string $status): string
    {
        return match ($status) {
            'awaiting_proof' => 'secondary',
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}

if (! function_exists('truncate_text')) {
    /**
     * Truncate text with ellipsis
     */
    function truncate_text(string $text, int $length = 100, string $ending = '...'): string
    {
        return Str::limit($text, $length, $ending);
    }
}

if (! function_exists('storage_url')) {
    /**
     * Get storage URL for a file
     */
    function storage_url(string $path, string $disk = 'public'): string
    {
        $path = ltrim($path, '/');

        return match ($disk) {
            'products' => '/storage/products/'.$path,
            'artisans' => '/storage/artisans/'.$path,
            'payments' => '/storage/payments/'.$path,
            'public' => '/storage/'.$path,
            default => Storage::disk($disk)->url($path),
        };
    }
}

if (! function_exists('active_link')) {
    /**
     * Check if current route matches and return 'active' class
     */
    function active_link(string|array $routes): string
    {
        $routes = is_array($routes) ? $routes : [$routes];

        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return 'active';
            }
        }

        return '';
    }
}

if (! function_exists('user_avatar')) {
    /**
     * Get user avatar URL or default
     */
    function user_avatar($user, int $size = 50): string
    {
        if ($user->isArtisan() && $user->artisanProfile?->profile_image) {
            return $user->artisanProfile->profile_image_url;
        }

        // Default avatar using UI Avatars
        $name = urlencode($user->name);

        return "https://ui-avatars.com/api/?name={$name}&size={$size}&background=random";
    }
}
