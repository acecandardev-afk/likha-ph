@extends('layouts.app')

@section('title', 'Register as artisan')

@section('main_class', 'pt-0 pb-0')

@section('content')
<div class="auth-ecommerce-landing auth-page--artisan">
    <div class="auth-ecommerce-bg" aria-hidden="true"></div>

    <div class="container-fluid position-relative px-3 px-lg-5 py-4 py-lg-5 auth-ecommerce-wrap">
        <div class="row g-4 g-xl-5 align-items-start justify-content-center auth-ecommerce-row auth-ecommerce-row--artisan">
            {{-- Story column --}}
            <div class="col-lg-5 col-xl-5">
                <div class="auth-artisan-hero">
                    <span class="auth-ecommerce-badge mb-3">
                        <i class="bi bi-shop-window me-2"></i>Artisan program
                    </span>
                    <h1 class="auth-artisan-hero__title mb-3">Become a verified seller</h1>
                    <p class="auth-ecommerce-lead mb-4">
                        Create your workshop profile, submit an ID photo for verification, then list products for review once approved.
                    </p>
                    <ul class="auth-ecommerce-perks list-unstyled mb-0">
                        <li><i class="bi bi-check2"></i>Product listings with an approval workflow</li>
                        <li><i class="bi bi-check2"></i>Orders and messages in one place</li>
                        <li><i class="bi bi-check2"></i>A profile that tells your workshop story</li>
                    </ul>
                    <p class="auth-artisan-hero__note small text-muted mt-4 mb-0">
                        Already selling elsewhere? You can still join—your workshop name and contact help buyers trust you.
                    </p>
                </div>
            </div>

            {{-- Form column --}}
            <div class="col-lg-7 col-xl-6">
                <div class="auth-panel-shell auth-panel-shell--wide">
                    <div class="position-relative z-1">
                        <div class="text-center text-md-start mb-4">
                            <h2 class="h4 mb-2">Create your seller application</h2>
                            <p class="text-body-secondary small mb-0">
                                Step 1: Submit your details. Step 2: Admin review. Step 3: Start selling after approval.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('register.artisan.store') }}" enctype="multipart/form-data" novalidate>
                            @csrf

                            <div class="auth-form-section">
                                <h3 class="auth-form-section__title">
                                    <span class="auth-form-section__icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                                    Your account
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="name" class="form-label">Full name <span class="text-danger">*</span></label>
                                        <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Juan Dela Cruz">
                                        @error('name')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="auth-form-section">
                                <h3 class="auth-form-section__title">
                                    <span class="auth-form-section__icon"><i class="bi bi-building" aria-hidden="true"></i></span>
                                    Workshop
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="workshop_name" class="form-label">Workshop / business name <span class="text-danger">*</span></label>
                                        <input id="workshop_name" type="text" class="form-control form-control-lg @error('workshop_name') is-invalid @enderror" name="workshop_name" value="{{ old('workshop_name') }}" required placeholder="e.g. Guihulngan Weaves">
                                        @error('workshop_name')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="phone" class="form-label">Phone <span class="text-muted fw-normal">(optional)</span></label>
                                        <input id="phone" type="tel" inputmode="tel" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="e.g. 09xx xxx xxxx">
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="auth-form-section">
                                <h3 class="auth-form-section__title">
                                    <span class="auth-form-section__icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                    Location
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12 col-sm-6">
                                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                        <select name="country" id="country" class="form-select form-control-lg @error('country') is-invalid @enderror" required>
                                            <option value="">Select country</option>
                                            <option value="Philippines" @selected(old('country', 'Philippines') === 'Philippines')>Philippines</option>
                                        </select>
                                        @error('country')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                                        <select name="region" id="region" class="form-select form-control-lg @error('region') is-invalid @enderror" required>
                                            <option value="">Select region</option>
                                        </select>
                                        @error('region')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                                        <select name="province" id="province" class="form-select form-control-lg @error('province') is-invalid @enderror" required disabled>
                                            <option value="">Select province</option>
                                        </select>
                                        @error('province')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                        <select name="city" id="city" class="form-select form-control-lg @error('city') is-invalid @enderror" required disabled>
                                            <option value="">Select city</option>
                                        </select>
                                        @error('city')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                                        <select name="barangay" id="barangay" class="form-select form-control-lg @error('barangay') is-invalid @enderror" required disabled>
                                            <option value="">Select barangay</option>
                                        </select>
                                        @error('barangay')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="street_address" class="form-label">Street, house no., landmarks <span class="text-muted fw-normal">(optional)</span></label>
                                        <textarea id="street_address" name="street_address" class="form-control @error('street_address') is-invalid @enderror" rows="3" placeholder="e.g. 123 Mabini St.">{{ old('street_address') }}</textarea>
                                        @error('street_address')
                                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="auth-form-section">
                                <h3 class="auth-form-section__title">
                                    <span class="auth-form-section__icon"><i class="bi bi-card-image" aria-hidden="true"></i></span>
                                    Identity verification
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="id_photo" class="form-label">Government-issued ID photo <span class="text-danger">*</span></label>
                                        <input
                                            id="id_photo"
                                            type="file"
                                            class="form-control @error('id_photo') is-invalid @enderror"
                                            name="id_photo"
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            required
                                        >
                                        <small class="text-muted d-block mt-2">
                                            JPG/PNG/WebP, up to 4MB. Upload a clear photo where your name and face are readable.
                                        </small>
                                        @error('id_photo')
                                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                        <div class="mt-3 d-none" id="idPhotoPreviewWrap">
                                            <div class="border rounded-3 p-2 bg-white">
                                                <div class="small text-muted mb-2">Preview</div>
                                                <img id="idPhotoPreview" alt="ID photo preview" class="img-fluid rounded-3" style="max-height: 220px; object-fit: contain;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="auth-form-section auth-form-section--last">
                                <h3 class="auth-form-section__title">
                                    <span class="auth-form-section__icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                                    Security
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                                        @error('password')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="password-confirm" class="form-label">Confirm password <span class="text-danger">*</span></label>
                                        <input id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border small mb-4">
                                Your application will be reviewed before your shop becomes active.
                            </div>

                            <div class="form-check mb-4 px-1">
                                <input type="checkbox" class="form-check-input @error('seller_terms_accepted') is-invalid @enderror" name="seller_terms_accepted" id="seller_terms_accepted_guest" value="1" {{ old('seller_terms_accepted') ? 'checked' : '' }} required>
                                <label class="form-check-label small" for="seller_terms_accepted_guest">
                                    I have read and agree to the <a href="{{ route('legal.seller-agreement') }}" target="_blank" rel="noopener noreferrer">seller terms &amp; marketplace policies</a>, including how promotional vouchers may affect seller payouts.
                                </label>
                                @error('seller_terms_accepted')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-auth-primary btn-lg w-100">
                                <i class="bi bi-person-plus me-2"></i>Submit application
                            </button>
                        </form>

                        <p class="text-center text-md-start text-body-secondary small mb-0 mt-4 pt-3 auth-panel-foot">
                            <a href="{{ route('login') }}" class="auth-panel-link">Log in</a>
                            <span class="text-muted mx-2">·</span>
                            <a href="{{ route('register') }}" class="auth-panel-link">Sign up as a shopper</a>
                            <span class="text-muted mx-2 d-none d-md-inline">·</span>
                            <a href="{{ route('home') }}" class="auth-panel-link d-block d-md-inline mt-2 mt-md-0">Back to home</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Page-specific spacing fix so footer is not too close to the artisan form tip/curve. */
.auth-page--artisan.auth-ecommerce-landing {
    margin-bottom: 0 !important;
    padding-bottom: 5rem !important;
}

.auth-page--artisan.auth-ecommerce-landing + .site-footer.site-footer--sub {
    margin-top: 1.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initIdPhotoPreview();
    initLocationSelectors({
        bootstrap: @json($phAddressBootstrap),
        savedCountry: @json(old('country', 'Philippines')),
        savedRegion: @json(old('region')),
        savedProvince: @json(old('province')),
        savedCity: @json(old('city')),
        savedBarangay: @json(old('barangay')),
    });
});

function initIdPhotoPreview() {
    var input = document.getElementById('id_photo');
    var wrap = document.getElementById('idPhotoPreviewWrap');
    var img = document.getElementById('idPhotoPreview');
    if (!input || !wrap || !img) return;

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];
        if (!file) {
            wrap.classList.add('d-none');
            img.removeAttribute('src');
            return;
        }

        if (!file.type || !file.type.startsWith('image/')) {
            wrap.classList.add('d-none');
            img.removeAttribute('src');
            return;
        }

        var url = URL.createObjectURL(file);
        img.src = url;
        wrap.classList.remove('d-none');
    });
}
</script>
@endpush
