@extends('layouts.app')

@section('title', 'Edit profile')

@section('content')
@php
    $user = $profile->user ?? auth()->user();
@endphp
<div class="artisan-profile-edit-page">
    <div class="container py-2 py-md-4">
        <x-profile-header-nav active="profile" />
        <div class="row g-4">
            {{-- Preview card (desktop) --}}
            <div class="col-12 col-lg-4 order-lg-2">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden sticky-top" style="top: 1rem;">
                    <div class="profile-preview-header position-relative">
                        @if($profile->profile_image ?? false)
                            <img src="{{ $profile->profile_image_url }}" alt="Profile" class="w-100" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="profile-preview-placeholder d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-person-badge text-white opacity-75" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                        <div class="position-absolute bottom-0 start-0 end-0 p-3 bg-gradient-to-top-dark">
                            <h3 class="h6 fw-semibold text-white mb-0 text-truncate" id="preview-workshop">{{ $profile->workshop_name ?: 'Your workshop name' }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">This is how your public profile will look. Changes save when you click “Save profile”.</p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <div class="col-12 col-lg-8 order-lg-1">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="profile-edit-icon rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-pencil-square text-white"></i>
                    </div>
                    <div>
                        <h1 class="h2 fw-semibold mb-1">Edit profile</h1>
                        <p class="text-body-secondary small mb-0">Update your workshop info so customers can find and trust you.</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('artisan.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="workshop_name" class="form-label fw-medium">Workshop / business name</label>
                                    <input type="text" name="workshop_name" id="workshop_name" class="form-control form-control-lg @error('workshop_name') is-invalid @enderror" value="{{ old('workshop_name', $profile->workshop_name) }}" required placeholder="e.g. Santos Bamboo Crafts">
                                    @error('workshop_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="story" class="form-label fw-medium">Your story <span class="text-muted fw-normal">(optional)</span></label>
                                    <textarea name="story" id="story" rows="4" class="form-control @error('story') is-invalid @enderror" placeholder="Tell customers about your craft, your journey, and what makes your work special.">{{ old('story', $profile->story) }}</textarea>
                                    @error('story')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="city_display" class="form-label fw-medium">City</label>
                                    <input type="text" id="city_display" class="form-control bg-body-secondary" value="{{ config('guihulngan.city_name') }}" readonly tabindex="-1" aria-readonly="true">
                                    <small class="text-muted">Deliveries are within Guihulngan City.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="barangay" class="form-label fw-medium">Barangay <span class="text-danger">*</span></label>
                                    <x-guihulngan.barangay-select name="barangay" id="barangay" :value="old('barangay', $profile->barangay)" :required="true" class="form-select @error('barangay') is-invalid @enderror" />
                                    @error('barangay')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="phone" class="form-label fw-medium">Phone</label>
                                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required placeholder="e.g. +63 912 345 6789">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label fw-medium">Street, house no., landmarks <span class="text-danger">*</span></label>
                                    <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $user->address) }}" required placeholder="e.g. Purok 3, near public market">
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="profile_image" class="form-label fw-medium">Profile image <span class="text-muted fw-normal">(optional)</span></label>
                                    <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg">
                                    <small class="text-muted">JPEG or PNG, max 2 MB. Recommended: square, at least 400×400px.</small>
                                    @error('profile_image')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    @if($profile->profile_image ?? false)
                                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                            <img src="{{ $profile->profile_image_url }}" alt="Current" class="rounded-3 border" style="max-height: 120px;">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#removeProfilePhotoModal">
                                                <i class="bi bi-trash me-1"></i> Remove profile picture
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-check-lg me-1"></i> Save profile
                                </button>
                                <a href="{{ route('artisan.dashboard') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Remove Profile Photo Confirmation Modal --}}
<div class="modal fade" id="removeProfilePhotoModal" tabindex="-1" aria-labelledby="removeProfilePhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div class="mb-3">
                    <i class="bi bi-person-x text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5 class="modal-title fw-semibold mb-2" id="removeProfilePhotoModalLabel">Remove profile picture?</h5>
                <p class="text-muted mb-4">Your profile picture will be removed. You can upload a new one at any time.</p>
                <form action="{{ route('artisan.profile.remove-photo') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Remove
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.artisan-profile-edit-page .card { border-radius: 1rem; }
.profile-edit-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}
.profile-preview-placeholder {
    background: linear-gradient(145deg, #6366f1 0%, #8b5cf6 100%);
}
.bg-gradient-to-top-dark {
    background: linear-gradient(to top, rgba(0,0,0,0.75), transparent);
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('workshop_name').addEventListener('input', function() {
    var el = document.getElementById('preview-workshop');
    if (el) el.textContent = this.value || 'Your workshop name';
});
</script>
@endpush
@endsection
