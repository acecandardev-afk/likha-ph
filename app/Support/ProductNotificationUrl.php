<?php

namespace App\Support;

/**
 * Detects in-app notification action URLs that point at a product (shop or artisan product pages).
 */
class ProductNotificationUrl
{
    public static function referencesProductId(?string $actionUrl, int $productId): bool
    {
        if (! is_string($actionUrl) || $actionUrl === '') {
            return false;
        }

        $path = parse_url($actionUrl, PHP_URL_PATH) ?? '';
        if ($path === '') {
            return false;
        }

        $id = (int) $productId;
        if ($id < 1) {
            return false;
        }

        // Public product page: /products/{id}
        if (preg_match('#/products/'.$id.'(?:$|[/?#])#', $path)) {
            return true;
        }

        // Artisan: /artisan/products/{id}, /artisan/products/{id}/edit
        return (bool) preg_match('#/artisan/products/'.$id.'(?:/edit)?(?:$|[/?#])#', $path);
    }
}
