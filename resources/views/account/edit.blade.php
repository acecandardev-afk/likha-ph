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
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="shipping_barangay" class="form-label fw-medium">Barangay</label>
                            <x-guihulngan.barangay-select name="shipping_barangay" id="shipping_barangay" :value="old('shipping_barangay', $user->shipping_barangay)" :required="false" class="form-select @error('shipping_barangay') is-invalid @enderror" empty-label="Select barangay" />
                            <small class="text-muted">Guihulngan City, {{ config('guihulngan.province') }}</small>
                            @error('shipping_barangay')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address" class="form-label fw-medium">Street, house no., landmarks</label>
                            <textarea name="shipping_address" id="shipping_address" rows="3" class="form-control @error('shipping_address') is-invalid @enderror" placeholder="Optional details to help couriers find you">{{ old('shipping_address', $user->shipping_address) }}</textarea>
                            <small class="text-muted">Leave blank if you prefer to enter everything at checkout.</small>
                            @error('shipping_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="shipping_phone" class="form-label fw-medium">Contact number for delivery</label>
                            <input type="text" name="shipping_phone" id="shipping_phone" class="form-control @error('shipping_phone') is-invalid @enderror" value="{{ old('shipping_phone', $user->shipping_phone) }}" placeholder="e.g. +63 912 345 6789">
                            @error('shipping_phone')
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
@endsection
