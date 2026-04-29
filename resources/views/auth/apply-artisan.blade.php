@extends('layouts.app')

@section('title', 'Apply as an artisan')

@section('content')
<div class="container py-3 py-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Apply to sell as an artisan</h1>
            <p class="text-muted mb-0">Fill in your workshop details. You can update the rest later in your artisan profile.</p>
        </div>
    </div>

    <div class="card border-0 shadow-soft-hover">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('register.artisan.store') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="workshop_name" class="form-label">Workshop / business name <span class="text-danger">*</span></label>
                    <input id="workshop_name" type="text" class="form-control form-control-lg @error('workshop_name') is-invalid @enderror" name="workshop_name" value="{{ old('workshop_name') }}" required autofocus placeholder="e.g. Guihulngan Weaves">
                    @error('workshop_name')
                        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="form-select @error('country') is-invalid @enderror" required>
                            <option value="">Select country</option>
                            <option value="Philippines" @selected(old('country', auth()->user()->country ?? 'Philippines') === 'Philippines')>Philippines</option>
                        </select>
                        @error('country')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                        <select name="region" id="region" class="form-select @error('region') is-invalid @enderror" required>
                            <option value="">Select region</option>
                        </select>
                        @error('region')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                        <select name="province" id="province" class="form-select @error('province') is-invalid @enderror" required disabled>
                            <option value="">Select province</option>
                        </select>
                        @error('province')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                        <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" required disabled>
                            <option value="">Select city</option>
                        </select>
                        @error('city')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select name="barangay" id="barangay" class="form-select @error('barangay') is-invalid @enderror" required disabled>
                            <option value="">Select barangay</option>
                        </select>
                        @error('barangay')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="street_address" class="form-label">Street, house no., landmarks <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="street_address" name="street_address" class="form-control @error('street_address') is-invalid @enderror" rows="3" placeholder="e.g. 123 Mabini St.">{{ old('street_address', auth()->user()->street_address ?? '') }}</textarea>
                        @error('street_address')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label for="id_photo" class="form-label">Government-issued ID photo <span class="text-danger">*</span></label>
                    <input
                        id="id_photo"
                        type="file"
                        class="form-control @error('id_photo') is-invalid @enderror"
                        name="id_photo"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        required
                    >
                    <small class="text-muted d-block mt-2">JPG/PNG/WebP, up to 4MB. Make sure the name and photo are readable.</small>
                    @error('id_photo')
                        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check mb-4 px-1 mt-3">
                    <input type="checkbox" class="form-check-input @error('seller_terms_accepted') is-invalid @enderror" name="seller_terms_accepted" id="seller_terms_accepted" value="1" {{ old('seller_terms_accepted') ? 'checked' : '' }} required>
                    <label class="form-check-label small" for="seller_terms_accepted">
                        I have read and agree to the <a href="{{ route('legal.seller-agreement') }}" target="_blank" rel="noopener noreferrer">seller terms &amp; marketplace policies</a>, including how promotional vouchers may affect seller payouts.
                    </label>
                    @error('seller_terms_accepted')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Submit application
                    </button>
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initLocationSelectors({
        bootstrap: @json($phAddressBootstrap),
        savedCountry: @json(old('country', auth()->user()->country ?? 'Philippines')),
        savedRegion: @json(old('region', auth()->user()->region ?? '')),
        savedProvince: @json(old('province', auth()->user()->province ?? '')),
        savedCity: @json(old('city', auth()->user()->city ?? '')),
        savedBarangay: @json(old('barangay', auth()->user()->barangay ?? '')),
    });
});
</script>
@endpush

