<?php

namespace App\Services;

use App\Models\Voucher;

class VoucherService
{
    /**
     * Resolve a voucher for a basket subtotal. Returns structured result for previews and persistence.
     *
     * @return array{voucher:Voucher|null, discount:float, error:string|null}
     */
    public function resolve(?string $rawCode, float $cartSubtotal): array
    {
        if ($cartSubtotal <= 0) {
            return ['voucher' => null, 'discount' => 0.0, 'error' => null];
        }

        $trimmed = $rawCode !== null ? trim($rawCode) : '';
        if ($trimmed === '') {
            return ['voucher' => null, 'discount' => 0.0, 'error' => null];
        }

        $code = strtoupper($trimmed);
        /** @var Voucher|null $voucher */
        $voucher = Voucher::query()->where('code', $code)->first();

        if (! $voucher) {
            return [
                'voucher' => null,
                'discount' => 0.0,
                'error' => 'That promo code was not recognized. Please check and try again.',
            ];
        }

        if (! $voucher->isCurrentlyValid()) {
            return [
                'voucher' => null,
                'discount' => 0.0,
                'error' => 'This promo code isn’t active right now.',
            ];
        }

        $discount = $voucher->computedDiscount($cartSubtotal);
        if ($discount <= 0) {
            return [
                'voucher' => null,
                'discount' => 0.0,
                'error' => 'This order doesn’t meet the minimum amount for that promo yet.',
            ];
        }

        return ['voucher' => $voucher, 'discount' => $discount, 'error' => null];
    }

    public function incrementRedemption(Voucher $voucher): void
    {
        $voucher->increment('times_redeemed');
    }
}
