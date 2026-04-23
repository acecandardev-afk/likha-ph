@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.cart.index') }}">Cart</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>
    </nav>
    <h2 class="h4 fw-semibold mb-2">Checkout</h2>
    <p class="text-muted small mb-4">Confirm your delivery details, choose payment, and place your order.</p>

    @if(empty($summary['items']) || $summary['total_items'] === 0)
        <div class="alert alert-info">
            Your cart is empty. <a href="{{ route('products.index') }}" class="alert-link">Add items to your cart</a> before checking out.
        </div>
    @else
        {{-- Checkout steps --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="d-flex align-items-center">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 28px; height: 28px;">1</span>
                        <span class="ms-2">Delivery</span>
                    </div>
                    <div class="flex-grow-1 border-top mx-2" style="min-width: 20px;"></div>
                    <div class="d-flex align-items-center">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 28px; height: 28px;">2</span>
                        <span class="ms-2">Payment</span>
                    </div>
                    <div class="flex-grow-1 border-top mx-2" style="min-width: 20px;"></div>
                    <div class="d-flex align-items-center">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 28px; height: 28px;">3</span>
                        <span class="ms-2">Review</span>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('customer.checkout.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    {{-- Step 1: Delivery address --}}
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2"></i> Delivery address</h5>
                        </div>
                        <div class="card-body">
                            @if(auth()->user()->country || auth()->user()->region || auth()->user()->province || auth()->user()->city || auth()->user()->barangay || auth()->user()->street_address || auth()->user()->phone)
                                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="useSavedAddress">
                                    <i class="bi bi-check2-circle me-1"></i> Use my saved address
                                </button>
                            @endif

                            <!-- Country (Fixed to Philippines) -->
                            <div class="mb-3">
                                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="Philippines" readonly>
                                <input type="hidden" name="country" value="Philippines">
                            </div>

                            {{-- Delivery is fixed: Guihulngan City only; region/province/city are server-set. --}}
                            <input type="hidden" name="region" value="{{ $delivery['region_id'] }}">
                            <input type="hidden" name="province" value="{{ $delivery['province_id'] }}">
                            <input type="hidden" name="city" value="{{ $delivery['city_id'] }}">

                            <div class="mb-3">
                                <label class="form-label">Region <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ $delivery['region_name'] }}" readonly tabindex="-1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Province <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ $delivery['province_name'] }}" readonly tabindex="-1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">City / municipality <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ $delivery['city_name'] }}" readonly tabindex="-1">
                                <small class="text-muted">Delivery is available within {{ $delivery['city_name'] }} only.</small>
                            </div>

                            <!-- Barangay (33 options for Guihulngan) -->
                            <div class="mb-3">
                                <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                                <select name="barangay" id="barangay" class="form-select @error('barangay') is-invalid @enderror" required>
                                    <option value="">Select barangay</option>
                                    @foreach($barangays as $b)
                                        <option value="{{ $b->id }}" @selected((string) $selectedBarangayId === (string) $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('barangay')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Street Address -->
                            <div class="mb-3">
                                <label for="street_address" class="form-label">Street, house no., landmarks <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea name="street_address" id="street_address" rows="3" class="form-control @error('street_address') is-invalid @enderror" placeholder="Helps couriers find your location">{{ old('street_address', auth()->user()->street_address) }}</textarea>
                                @error('street_address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="mb-0">
                                <label for="phone" class="form-label">Contact number for delivery <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}" required placeholder="09XXXXXXXXX or +63XXXXXXXXXXX">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="text-muted">Save your address in <a href="{{ route('account.edit') }}">Account settings</a> for faster checkout next time.</small>
                        </div>
                    </div>

                    {{-- Step 2: Payment method --}}
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-semibold"><i class="bi bi-credit-card me-2"></i> Payment method</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-0">
                                <select name="payment_method" class="form-select form-select-lg @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Choose payment method</option>
                                    <option value="cod" {{ old('payment_method') === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank transfer</option>
                                    <option value="gcash" {{ old('payment_method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash on pickup</option>
                                </select>
                                <small class="text-muted d-block mt-2">With COD, pay when your order is delivered. For bank transfer and GCash, you will upload payment proof after placing the order.</small>
                                @error('payment_method')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Order notes --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <label for="customer_notes" class="form-label">Order notes <span class="text-muted">(optional)</span></label>
                            <textarea name="customer_notes" id="customer_notes" rows="3" class="form-control" placeholder="Special instructions for your order">{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Order summary & place order --}}
                <div class="col-12 col-lg-4">
                    <div class="card sticky-top">
                        <div class="card-header">
                            <h5 class="mb-0 fw-semibold">Order summary</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $platformFeeRate = (float) config('fees.platform_fee_rate', 0.05);
                                $platformFee = round(($summary['subtotal'] ?? 0) * $platformFeeRate, 2);
                                $grandTotal = ($summary['subtotal'] ?? 0) + $platformFee;
                            @endphp
                            @foreach($summary['grouped_by_artisan'] as $artisanId => $items)
                                <div class="mb-3">
                                    <small class="text-muted fw-semibold">
                                        {{ $items->first()->product->artisan?->artisanProfile?->workshop_name
                                            ?? $items->first()->product->artisan?->name
                                            ?? 'Unknown Artisan' }}
                                    </small>
                                    @foreach($items as $item)
                                        <div class="d-flex justify-content-between align-items-start small mt-1">
                                            <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                                            <span>₱{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            <hr>
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₱{{ number_format($summary['subtotal'], 2) }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Platform fee ({{ (int) round($platformFeeRate * 100) }}%)</span><span>₱{{ number_format($platformFee, 2) }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span class="text-muted small">Calculated later</span></div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4"><span class="fw-semibold">Total</span><span class="fw-bold fs-5">₱{{ number_format($grandTotal, 2) }}</span></div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="bi bi-check-circle me-1"></i> Place order</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initGuihulnganCheckoutForm({
        useSavedButtonId: 'useSavedAddress',
        barangaySelectId: 'barangay',
        streetFieldId: 'street_address',
        phoneFieldId: 'phone',
        saved: {
            barangay: @json(auth()->user()->barangay ?? ''),
            street: @json(auth()->user()->street_address ?? ''),
            phone: @json(auth()->user()->phone ?? '')
        }
    });
});
</script>
@endpush
@endsection
