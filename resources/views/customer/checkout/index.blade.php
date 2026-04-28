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
    <p class="text-muted small mb-4">Confirm your delivery details, group items into packages if needed (each package may ship separately), and place your order. Payment is cash on delivery (COD).</p>

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
                        <span class="ms-2">Packages</span>
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

                            <input type="hidden" id="co_region" name="region" value="{{ old('region', $selectedRegionId ?? '') }}">
                            <input type="hidden" id="co_province" name="province" value="{{ old('province', $selectedProvinceId ?? '') }}">
                            <input type="hidden" id="co_city" name="city" value="{{ old('city', $selectedCityId ?? '') }}">
                            <input type="hidden" id="co_barangay" name="barangay" value="{{ old('barangay', $selectedBarangayId ?? '') }}">

                            <div class="mb-3">
                                <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                                <select id="region" class="form-select @error('region') is-invalid @enderror" required>
                                    <option value="">Select region</option>
                                </select>
                                @error('region')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                                <select id="province" class="form-select @error('province') is-invalid @enderror" required disabled>
                                    <option value="">Select province</option>
                                </select>
                                @error('province')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="city" class="form-label">City / municipality <span class="text-danger">*</span></label>
                                <select id="city" class="form-select @error('city') is-invalid @enderror" required disabled>
                                    <option value="">Select city</option>
                                </select>
                                @error('city')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                                <select id="barangay" class="form-select @error('barangay') is-invalid @enderror" required disabled>
                                    <option value="">Select barangay</option>
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

                    <input type="hidden" name="payment_method" value="cod">

                    {{-- Step 2: Package groups per artisan --}}
                    @foreach($summary['grouped_by_artisan'] as $artisanId => $shopItems)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0 fw-semibold"><i class="bi bi-box-seam me-2"></i> Delivery packages — {{ $shopItems->first()->product->artisan?->artisanProfile?->workshop_name ?? $shopItems->first()->product->artisan?->name ?? 'Seller' }}</h5>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted">Assign each line to package <strong>1</strong>, <strong>2</strong>, … Separate packages may be assigned to different riders (up to 5 active stops per rider).</p>
                                @foreach($shopItems as $cartItem)
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 border-bottom">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-medium small">{{ $cartItem->product->name }}</div>
                                            <div class="text-muted small">Qty {{ $cartItem->quantity }} · ₱{{ number_format($cartItem->product->price * $cartItem->quantity, 2) }}</div>
                                        </div>
                                        <div style="min-width: 140px;">
                                            <label class="visually-hidden" for="pkg_{{ $artisanId }}_{{ $cartItem->id }}">Package #</label>
                                            <select name="package_split[{{ $artisanId }}][{{ $cartItem->id }}]" id="pkg_{{ $artisanId }}_{{ $cartItem->id }}" class="form-select form-select-sm">
                                                @for($p = 1; $p <= 5; $p++)
                                                    <option value="{{ $p }}" @selected((int) old('package_split.'.$artisanId.'.'.$cartItem->id, 1) === $p)>Package {{ $p }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

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
    initCheckoutAddressForm({
        bootstrap: @json($phAddressBootstrap ?? null),
        useSavedButtonId: 'useSavedAddress',
        streetFieldId: 'street_address',
        phoneFieldId: 'phone',
        savedForButton: {
            region: @json($selectedRegionId ?? null),
            province: @json($selectedProvinceId ?? null),
            city: @json($selectedCityId ?? null),
            barangay: @json(auth()->user()->barangay ?? ''),
            street: @json(auth()->user()->street_address ?? ''),
            phone: @json(auth()->user()->phone ?? '')
        }
    });
});
</script>
@endpush
@endsection
