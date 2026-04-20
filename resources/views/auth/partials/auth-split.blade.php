@php
    $activeAuthTab = $initialTab ?? 'login';
    if ($errors->any() && filled(old('name'))) {
        $activeAuthTab = 'register';
    }
    $googleConfigured = filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
@endphp

<div class="auth-ecommerce-landing">
    <div class="auth-ecommerce-bg" aria-hidden="true"></div>
    <div class="auth-ecommerce-blob auth-ecommerce-blob--1" aria-hidden="true"></div>
    <div class="auth-ecommerce-blob auth-ecommerce-blob--2" aria-hidden="true"></div>
    <div class="container position-relative px-3 px-lg-4 auth-ecommerce-wrap auth-ecommerce-wrap--login">
        <div class="row g-3 g-lg-4 align-items-start justify-content-between auth-ecommerce-row auth-ecommerce-row--login">
            <div class="col-12 col-lg-5 col-xl-5">
                <div class="auth-ecommerce-hero">
                    <div class="auth-ecommerce-hero-inner text-center text-lg-start">
                        <p class="auth-ecommerce-badge mb-2">
                            <img
                                src="{{ asset('likha-ph-logo.png') }}"
                                alt="{{ config('app.name', 'Likha PH') }} logo"
                                width="22"
                                height="22"
                                class="me-1"
                                loading="eager"
                                decoding="async"
                                style="object-fit:contain;"
                            >{{ config('app.name', 'Likha PH') }} Marketplace
                        </p>
                        <h1 class="auth-ecommerce-title mb-2">
                            Handcrafted finds from Filipino artisans
                        </h1>
                        <p class="auth-ecommerce-tagline mb-3">Shop with purpose.</p>
                        <p class="auth-ecommerce-lead mb-3">
                            Browse listings from Guihulngan makers. Sign in to checkout, track orders, and message sellers.
                        </p>
                        <ul class="auth-ecommerce-perks auth-ecommerce-perks--minimal list-unstyled mb-0">
                            <li><i class="bi bi-check-lg" aria-hidden="true"></i>Secure checkout &amp; order tracking</li>
                            <li><i class="bi bi-check-lg" aria-hidden="true"></i>Local crafts &amp; artisan profiles</li>
                            <li><i class="bi bi-check-lg" aria-hidden="true"></i>Direct support for creators</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-10 col-lg-6 col-xl-5 offset-md-1 offset-lg-0">
                <div class="auth-panel-shell">
                    <div class="auth-tab-switch" role="tablist" aria-label="Account">
                        <button type="button"
                                class="auth-tab-btn {{ $activeAuthTab === 'login' ? 'active' : '' }}"
                                id="auth-tab-login"
                                data-auth-tab="login"
                                role="tab"
                                aria-selected="{{ $activeAuthTab === 'login' ? 'true' : 'false' }}"
                                aria-controls="auth-panel-login">
                            Log in
                        </button>
                        <button type="button"
                                class="auth-tab-btn {{ $activeAuthTab === 'register' ? 'active' : '' }}"
                                id="auth-tab-register"
                                data-auth-tab="register"
                                role="tab"
                                aria-selected="{{ $activeAuthTab === 'register' ? 'true' : 'false' }}"
                                aria-controls="auth-panel-register">
                            Sign up
                        </button>
                    </div>

                    <div class="auth-oauth-block {{ $googleConfigured ? '' : 'auth-oauth-block--inactive' }}">
                        @if($googleConfigured)
                            <p class="text-center small text-body-secondary mb-2 mb-md-2">Or continue with</p>
                            <div class="d-grid">
                                <a href="{{ route('auth.google') }}" class="btn btn-auth-google">
                                    <i class="bi bi-google me-2" aria-hidden="true"></i>Google
                                </a>
                            </div>
                        @else
                            <p class="small text-body-secondary text-center mb-0 lh-sm">
                                Google sign-in is disabled until you add <code class="px-1">GOOGLE_CLIENT_ID</code> and <code class="px-1">GOOGLE_CLIENT_SECRET</code> to <code class="px-1">.env</code>.
                            </p>
                        @endif
                    </div>

                    <div id="auth-panel-login"
                         class="auth-tab-panel {{ $activeAuthTab !== 'login' ? 'd-none' : '' }}"
                         data-auth-panel="login"
                         role="tabpanel"
                         aria-labelledby="auth-tab-login"
                         @if($activeAuthTab !== 'login') hidden @endif>
                        <div class="text-center mb-3">
                            <h2 class="h5 fw-semibold mb-1">Welcome back</h2>
                            <p class="text-body-secondary small mb-0">Sign in to continue shopping and track your orders.</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="login-email" class="form-label">Email</label>
                                <input id="login-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" {{ $activeAuthTab === 'login' ? 'autofocus' : '' }} placeholder="you@example.com">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="login-password" class="form-label">Password</label>
                                <input id="login-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="text-decoration-none small auth-panel-link" href="{{ route('password.request') }}">Forgot password?</a>
                                @endif
                            </div>

                            <button type="submit" class="btn btn-auth-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Log in
                            </button>
                        </form>
                    </div>

                    <div id="auth-panel-register"
                         class="auth-tab-panel {{ $activeAuthTab !== 'register' ? 'd-none' : '' }}"
                         data-auth-panel="register"
                         role="tabpanel"
                         aria-labelledby="auth-tab-register"
                         @if($activeAuthTab !== 'register') hidden @endif>
                        <div class="text-center mb-3">
                            <h2 class="h5 fw-semibold mb-1">Create your account</h2>
                            <p class="text-body-secondary small mb-0">Join to shop, save favorites, and checkout faster.</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="register-name" class="form-label">Full name</label>
                                <input id="register-name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" {{ $activeAuthTab === 'register' ? 'autofocus' : '' }} placeholder="Your name">
                                @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="register-email" class="form-label">Email</label>
                                <input id="register-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="register-password" class="form-label">Password</label>
                                <input id="register-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password-confirm" class="form-label">Confirm password</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                            </div>

                            <p class="small text-body-secondary mb-3">
                                By signing up, you agree to our
                                <a href="#" class="auth-panel-link fw-medium">Terms</a>
                                and
                                <a href="#" class="auth-panel-link fw-medium">Privacy Policy</a>.
                            </p>

                            <button type="submit" class="btn btn-auth-primary w-100">
                                <i class="bi bi-person-plus me-2"></i>Create account
                            </button>
                        </form>
                    </div>

                    <p class="text-center text-body-secondary small mb-0 mt-3 pt-2 auth-panel-foot">
                        Selling handmade goods?
                        <a href="{{ route('register.artisan') }}" class="auth-panel-link fw-medium">Register as an artisan</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('.auth-tab-btn[data-auth-tab]');
    var panels = document.querySelectorAll('[data-auth-panel]');
    function setTab(name) {
        panels.forEach(function (p) {
            var on = p.getAttribute('data-auth-panel') === name;
            p.classList.toggle('d-none', !on);
            p.toggleAttribute('hidden', !on);
        });
        buttons.forEach(function (b) {
            var on = b.getAttribute('data-auth-tab') === name;
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
    }
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setTab(btn.getAttribute('data-auth-tab'));
        });
    });
});
</script>
@endpush
