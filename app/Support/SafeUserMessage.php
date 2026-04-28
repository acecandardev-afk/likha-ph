<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Maps domain exceptions to strings safe to show in flashes (no stack traces or DB details).
 */
final class SafeUserMessage
{
    /**
     * Known messages from {@see \App\Services\DeliveryService::updateDeliveryStatus}.
     */
    public static function forDeliveryInvalidArgument(InvalidArgumentException $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'already been delivered')) {
            return 'This package has already been delivered. Its status can no longer be changed.';
        }

        if (str_contains($message, 'Invalid delivery status')) {
            return 'That delivery status is not valid for this package.';
        }

        return 'Something went wrong while updating delivery. Please refresh and try again.';
    }
}
