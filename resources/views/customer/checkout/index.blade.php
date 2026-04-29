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
    <p class="text-muted small mb-4">Confirm where to send your order, split items into packages if you need more than one box from a seller, then review the total. You’ll pay when your order arrives.</p>

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

        {{-- Separate forms for quantity updates (cannot nest forms inside checkout submit form) --}}
        @foreach($summary['items'] as $qtyCartItem)
            <form id="checkout-qty-{{ $qtyCartItem->id }}" action="{{ route('customer.cart.update', $qtyCartItem) }}" method="POST" class="d-none" aria-hidden="true">
                @csrf
                @method('PATCH')
            </form>
        @endforeach

        <form action="{{ route('customer.checkout.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    @if($errors->has('quantity'))
                        <div class="alert alert-danger small mb-3" role="alert">
                            Please choose a quantity within available stock for each item.
                        </div>
                    @endif

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
                                <p class="small text-muted">Change quantity below if needed (updates your cart). Then assign each line to package <strong>1</strong>, <strong>2</strong>, … Separate packages may be assigned to different riders (up to 5 active stops per rider).</p>
                                @foreach($shopItems as $cartItem)
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 border-bottom">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-medium small">{{ $cartItem->product->name }}</div>
                                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                <label class="small text-muted mb-0" for="checkout_qty_{{ $cartItem->id }}">Qty</label>
                                                <input type="number"
                                                       id="checkout_qty_{{ $cartItem->id }}"
                                                       name="quantity"
                                                       form="checkout-qty-{{ $cartItem->id }}"
                                                       value="{{ $cartItem->quantity }}"
                                                       min="1"
                                                       max="{{ $cartItem->product->stock }}"
                                                       class="form-control form-control-sm"
                                                       style="width: 4.25rem;"
                                                       onchange="this.form && this.form.submit()"
                                                       title="Updates cart when changed">
                                                <span class="text-muted small">· ₱{{ number_format($cartItem->product->price, 2) }} each · max {{ $cartItem->product->stock }}</span>
                                            </div>
                                            <div class="small fw-semibold mt-1">Line total: ₱{{ number_format($cartItem->product->price * $cartItem->quantity, 2) }}</div>
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
                    <div class="card sticky-top" style="top: 1rem; z-index: 1;">
                        <div class="card-header">
                            <h5 class="mb-0 fw-semibold">Order summary</h5>
                        </div>
                        <div class="card-body">
                            @php $totals = $checkoutPreview ?? []; @endphp
                            @foreach($summary['grouped_by_artisan'] as $artisanId => $items)
                                <div class="mb-3">
                                    <small class="text-muted fw-semibold">
                                        {{ $items->first()->product->artisan?->artisanProfile?->workshop_name
                                            ?? $items->first()->product->artisan?->name
                                            ?? 'Seller' }}
                                    </small>
                                    @foreach($items as $item)
                                        <div class="d-flex justify-content-between align-items-start small mt-1">
                                            <span class="pe-2">{{ $item->product->name }} × {{ $item->quantity }}</span>
                                            <span class="text-nowrap">₱{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            <div class="mb-3">
                                <label for="voucher_code" class="form-label small mb-1">Promo code <span class="text-muted">(optional)</span></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="voucher_code" id="voucher_code" class="form-control @error('voucher_code') is-invalid @enderror" value="{{ old('voucher_code') }}" autocomplete="off" placeholder="Enter a code" maxlength="40" inputmode="text">
                                    <button type="button" class="btn btn-outline-secondary" id="checkout-apply-promo">Apply</button>
                                </div>
                                @error('voucher_code')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                                <div id="checkout-promo-feedback" class="small mt-1 text-muted" aria-live="polite"></div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2 small"><span>Items</span><span class="text-nowrap">₱<span id="checkout-summary-subtotal">{{ number_format($totals['subtotal'] ?? $summary['subtotal'], 2) }}</span></span></div>
                            <div id="checkout-summary-discount-row" class="d-flex justify-content-between mb-2 small text-success {{ ($totals['discount'] ?? 0) > 0 ? '' : 'd-none' }}"><span id="checkout-summary-promo-label">Promo</span><span class="text-nowrap">−₱<span id="checkout-summary-discount">{{ number_format($totals['discount'] ?? 0, 2) }}</span></span></div>
                            <div class="d-flex justify-content-between mb-2 small"><span>Service fee</span><span class="text-nowrap">₱<span id="checkout-summary-service">{{ number_format($totals['service_fee_total'] ?? 0, 2) }}</span></span></div>
                            <div id="checkout-summary-delivery-row" class="d-flex justify-content-between mb-2 small {{ ($totals['delivery_total'] ?? 0) > 0 ? '' : 'd-none' }}"><span>Delivery</span><span class="text-nowrap">₱<span id="checkout-summary-delivery">{{ number_format($totals['delivery_total'] ?? 0, 2) }}</span></span></div>
                            <div id="checkout-summary-tax-row" class="d-flex justify-content-between mb-2 small {{ ($totals['taxes_total'] ?? 0) > 0 ? '' : 'd-none' }}"><span>Taxes</span><span class="text-nowrap">₱<span id="checkout-summary-tax">{{ number_format($totals['taxes_total'] ?? 0, 2) }}</span></span></div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3"><span class="fw-semibold">Total</span><span class="fw-bold fs-5 text-nowrap">₱<span id="checkout-summary-grand">{{ number_format($totals['grand_total'] ?? 0, 2) }}</span></span></div>
                            @if(($totals['seller_count'] ?? 1) > 1 && ($totals['delivery_total'] ?? 0) > 0)
                                <p class="small text-muted mb-3">Delivery includes one stop per seller in your cart.</p>
                            @endif
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('checkout-apply-promo');
    var input = document.getElementById('voucher_code');
    var feedback = document.getElementById('checkout-promo-feedback');
    var previewUrl = @json(route('customer.checkout.preview-totals'));
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!btn || !input || !previewUrl || !token) return;

    function pesoFmt(n) {
        try {
            return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(n || 0));
        } catch (e) {
            return Number(n || 0).toFixed(2);
        }
    }

    function applyTotals(data) {
        function setText(id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = pesoFmt(val);
        }
        setText('checkout-summary-subtotal', data.subtotal);
        setText('checkout-summary-discount', data.discount);
        setText('checkout-summary-service', data.service_fee_total);
        setText('checkout-summary-delivery', data.delivery_total);
        setText('checkout-summary-tax', data.taxes_total);
        setText('checkout-summary-grand', data.grand_total);

        var dr = document.getElementById('checkout-summary-discount-row');
        if (dr) {
            if (Number(data.discount || 0) > 0) dr.classList.remove('d-none');
            else dr.classList.add('d-none');
        }
        var lbl = document.getElementById('checkout-summary-promo-label');
        if (lbl && data.voucher_label) {
            lbl.textContent = 'Promo (' + String(data.voucher_label) + ')';
        } else if (lbl) {
            lbl.textContent = 'Promo';
        }

        var delRow = document.getElementById('checkout-summary-delivery-row');
        if (delRow) {
            if (Number(data.delivery_total || 0) > 0) delRow.classList.remove('d-none');
            else delRow.classList.add('d-none');
        }
        var taxRow = document.getElementById('checkout-summary-tax-row');
        if (taxRow) {
            if (Number(data.taxes_total || 0) > 0) taxRow.classList.remove('d-none');
            else taxRow.classList.add('d-none');
        }
    }

    btn.addEventListener('click', function () {
        feedback.textContent = '';
        btn.disabled = true;
        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ voucher_code: input.value.trim() })
        }).then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, status: res.status, data: data };
            });
        }).then(function (payload) {
            if (!payload.ok) {
                var msg = (payload.data && payload.data.message) ? payload.data.message : 'Could not update totals.';
                feedback.textContent = msg;
                feedback.classList.remove('text-success');
                feedback.classList.add('text-danger');
                return;
            }

            applyTotals(payload.data);

            if (payload.data.voucher_error) {
                feedback.textContent = payload.data.voucher_error;
                feedback.classList.remove('text-success');
                feedback.classList.add('text-danger');
                return;
            }

            if (input.value.trim() !== '') {
                feedback.textContent = payload.data.voucher_label ? ('Applied: ' + payload.data.voucher_label) : 'Promo applied.';
                feedback.classList.remove('text-danger');
                feedback.classList.add('text-success');
            } else {
                feedback.textContent = '';
            }
        }).catch(function () {
            feedback.textContent = 'Could not reach the server. Try again.';
            feedback.classList.remove('text-success');
            feedback.classList.add('text-danger');
        }).finally(function () {
            btn.disabled = false;
        });
    });
});
</script>
@endpush
@endsection
