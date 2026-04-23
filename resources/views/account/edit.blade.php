@extends('layouts.app')

@section('title', 'Shipping address')

@section('content')
<div class="container py-2 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <x-profile-header-nav active="shipping" />
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h1 class="h2 fw-semibold mb-1">Shipping address</h1>
                    <p class="text-body-secondary small mb-0">Set your default delivery address. It will be used to auto-fill during checkout.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 p-md-5">
                    @if(!empty($addressUnavailable))
                        <div class="alert alert-warning">Address data is not available yet. Please try again later or contact support.</div>
                    @endif

                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        @error('error')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <!-- Country (Fixed to Philippines) -->
                        <div class="mb-3">
                            <label for="country" class="form-label fw-medium">Country</label>
                            <input type="text" class="form-control" value="Philippines" readonly>
                            <input type="hidden" name="country" value="Philippines">
                        </div>

                        @isset($delivery, $barangays)
                            <input type="hidden" name="region" value="{{ $delivery['region_id'] }}">
                            <input type="hidden" name="province" value="{{ $delivery['province_id'] }}">
                            <input type="hidden" name="city" value="{{ $delivery['city_id'] }}">

                            <div class="mb-3">
                                <label class="form-label fw-medium">Region</label>
                                <input type="text" class="form-control" value="{{ $delivery['region_name'] }}" readonly tabindex="-1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Province</label>
                                <input type="text" class="form-control" value="{{ $delivery['province_name'] }}" readonly tabindex="-1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">City / municipality</label>
                                <input type="text" class="form-control" value="{{ $delivery['city_name'] }}" readonly tabindex="-1">
                                <small class="text-muted">Default delivery is within {{ $delivery['city_name'] }}.</small>
                            </div>

                            <div class="mb-3">
                                <label for="barangay" class="form-label fw-medium">Barangay</label>
                                <select name="barangay" id="barangay" class="form-select @error('barangay') is-invalid @enderror">
                                    <option value="">Select barangay (optional)</option>
                                    @foreach($barangays as $b)
                                        <option value="{{ $b->id }}" @selected((string) ($selectedBarangayId ?? '') === (string) $b->id)>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('barangay')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endisset

                        <!-- Street Address -->
                        <div class="mb-3">
                            <label for="street_address" class="form-label fw-medium">Street, house no., landmarks</label>
                            <textarea name="street_address" id="street_address" rows="3" class="form-control @error('street_address') is-invalid @enderror" placeholder="Optional details to help couriers find you">{{ old('street_address', $user->street_address) }}</textarea>
                            <small class="text-muted">Leave blank if you prefer to enter everything at checkout.</small>
                            @error('street_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="form-label fw-medium">Contact number for delivery</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="09XXXXXXXXX or +63XXXXXXXXXXX">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if(isset($delivery, $barangays))
<script>
document.addEventListener('DOMContentLoaded', function() {
    initGuihulnganCheckoutForm({
        barangaySelectId: 'barangay',
        streetFieldId: 'street_address',
        phoneFieldId: 'phone',
        saved: {
            barangay: @json($user->barangay ?? ''),
            street: @json($user->street_address ?? ''),
            phone: @json($user->phone ?? '')
        }
    });
});
</script>
@endif
@endpush
@endsection
