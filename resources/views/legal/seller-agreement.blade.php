@extends('layouts.app')

@section('title', 'Seller terms & marketplace policies')

@section('content')
<div class="container py-4 py-md-5 col-lg-10 col-xl-9">
    <h1 class="h2 fw-semibold mb-3">Seller terms & marketplace policies</h1>
    <p class="text-muted mb-4">
        Last updated {{ now()->format('F j, Y') }}. Please read carefully before applying as an artisan seller on Likha PH.
    </p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-md-5 prose-readable">
            <h2 class="h5 fw-semibold mt-0">1. Eligibility & verification</h2>
            <p class="small">
                By submitting an application, you confirm that information about your workshop and identity documents is accurate.
                Likha PH may approve or decline applications and request additional verification at its discretion.
            </p>

            <h2 class="h5 fw-semibold mt-4">2. Listings & fulfilment</h2>
            <p class="small">
                You are responsible for accurate product descriptions, pricing, stock levels, and timely fulfilment of orders assigned to your shop.
                Misleading listings or repeated cancellations may result in suspension.
            </p>

            <h2 class="h5 fw-semibold mt-4">3. Fees & payouts</h2>
            <p class="small">
                Service fees and settlement timing follow the rates and policies displayed in your seller dashboard and admin communications.
                Platform fees apply to qualifying merchandise totals as configured by Likha PH.
            </p>

            <h2 class="h5 fw-semibold mt-4">4. Promotional vouchers & discounts</h2>
            <p class="small mb-2">
                Likha PH may issue promotional codes (“vouchers”) that reduce what buyers pay at checkout when requirements are met (minimum spend,
                validity dates, redemption limits).
            </p>
            <ul class="small">
                <li>Vouchers apply to qualifying baskets according to platform rules and cannot be merged unless explicitly stated.</li>
                <li>
                    Unless a promotion explicitly states otherwise, merchandise discounts reduce the net merchandise total before platform fees are calculated,
                    which may affect seller proceeds compared with non-promotional orders.
                </li>
                <li>Sellers acknowledge that marketplace-managed promotions are part of operating on Likha PH and agree not to circumvent verified checkout flows.</li>
            </ul>

            <h2 class="h5 fw-semibold mt-4">5. Messaging & conduct</h2>
            <p class="small">
                Use order messaging respectfully. Harassment, fraud, or attempts to move transactions off-platform may lead to account action.
            </p>

            <h2 class="h5 fw-semibold mt-4">6. Changes</h2>
            <p class="small mb-0">
                Likha PH may update these policies; continued selling after notice constitutes acceptance where permitted by law.
            </p>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3">
        <a href="{{ route('register.artisan') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to seller application
        </a>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">Home</a>
    </div>
</div>
@endsection

@push('styles')
<style>
.prose-readable { max-width: 52rem; }
.prose-readable p:last-child { margin-bottom: 0; }
</style>
@endpush
