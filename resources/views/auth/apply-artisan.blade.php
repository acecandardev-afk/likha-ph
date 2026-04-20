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
                        <label for="city_display" class="form-label">City</label>
                        <input id="city_display" type="text" class="form-control bg-body-secondary" value="{{ config('guihulngan.city_name') }}" readonly tabindex="-1" aria-readonly="true">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                        <x-guihulngan.barangay-select name="barangay" id="barangay" :value="old('barangay')" :required="true" class="form-select @error('barangay') is-invalid @enderror" empty-label="Select barangay" />
                        @error('barangay')
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
@endsection

