<?php

return [
    /**
     * Platform fee applied on every checkout.
     * Stored per-order as `orders.platform_fee`.
     */
    'platform_fee_rate' => 0.05,

    /**
     * Amount credited to the rider when a package is marked delivered (PHP).
     * Stored on `order_packages.rider_fee_amount` at delivery time.
     */
    'rider_fee_per_package' => (float) env('RIDER_FEE_PER_PACKAGE', 50),
];

