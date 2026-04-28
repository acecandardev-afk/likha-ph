@php
    $disc = (float) ($order->discount_amount ?? 0);
    $ship = (float) ($order->shipping_amount ?? 0);
    $tax = (float) ($order->tax_amount ?? 0);
    $pf = (float) ($order->platform_fee ?? 0);
@endphp
<div class="d-flex justify-content-between mb-2"><span>Items</span><span>₱{{ number_format($order->subtotal, 2) }}</span></div>
@if($disc > 0)
    <div class="d-flex justify-content-between mb-2 text-success"><span>Promo savings</span><span>−₱{{ number_format($disc, 2) }}</span></div>
@endif
<div class="d-flex justify-content-between mb-2"><span>Service fee</span><span>₱{{ number_format($pf, 2) }}</span></div>
@if($ship > 0)
    <div class="d-flex justify-content-between mb-2"><span>Delivery</span><span>₱{{ number_format($ship, 2) }}</span></div>
@endif
@if($tax > 0)
    <div class="d-flex justify-content-between mb-2"><span>Taxes</span><span>₱{{ number_format($tax, 2) }}</span></div>
@endif
<hr class="my-2">
<div class="d-flex justify-content-between mb-2"><span class="fw-semibold">Total to pay</span><span class="fw-bold">₱{{ number_format($order->total, 2) }}</span></div>
@if($order->voucher_code)
    <p class="small text-muted mb-0">Promo code: {{ $order->voucher_code }}</p>
@endif
