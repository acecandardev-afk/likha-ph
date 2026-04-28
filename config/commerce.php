<?php

/**
 * Checkout and storefront commerce settings (plain-language labels in UI).
 */

return [
    /**
     * One delivery fee per seller order when the cart includes items from multiple sellers.
     * Amount in Philippine pesos.
     */
    'shipping_flat_per_order' => (float) env('COMMERCE_SHIPPING_FLAT', 49),

    /**
     * Applied to merchandise after promotions plus delivery charge (unless rate is zero).
     * Example: 0.12 means 12% — set to 0 to hide tax lines until you configure it.
     */
    'tax_rate' => (float) env('COMMERCE_TAX_RATE', 0),
];
