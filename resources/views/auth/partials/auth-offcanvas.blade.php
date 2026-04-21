@php
    $ocTab = $errors->any() && filled(old('name')) ? 'register' : 'login';
@endphp

<div class="offcanvas offcanvas-end likha-auth-offcanvas" tabindex="-1" id="likhaAuthPanel" aria-labelledby="likhaAuthLabel" data-bs-scroll="true">
    <div class="offcanvas-header border-bottom border-opacity-10">
        <div class="d-flex align-items-center gap-2">
            <img
                src="{{ asset('likha-ph-logo.png') }}"
                alt="{{ config('app.name', 'Likha PH') }} logo"
                width="32"
                height="32"
                loading="eager"
                decoding="async"
                style="object-fit:contain;"
            >
            <div>
                <p class="small text-uppercase text-muted mb-0 letter-spacing-sm" id="likhaAuthLabel">{{ config('app.name') }}</p>
                <h2 class="h5 mb-0 fw-bold">Account</h2>
            </div>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3 p-md-4">
        <div class="auth-panel-shell auth-panel-shell--offcanvas position-relative z-1">
            <div class="auth-tab-switch auth-tab-switch--offcanvas" role="tablist" aria-label="Sign in or sign up">
                <button type="button"
                        class="auth-tab-btn {{ $ocTab === 'login' ? 'active' : '' }}"
                        id="oc-auth-tab-login"
                        data-auth-tab="login"
                        data-oc-auth-tab
                        role="tab"
                        aria-selected="{{ $ocTab === 'login' ? 'true' : 'false' }}">
                    Sign in
                </button>
                <button type="button"
                        class="auth-tab-btn {{ $ocTab === 'register' ? 'active' : '' }}"
                        id="oc-auth-tab-register"
                        data-auth-tab="register"
                        data-oc-auth-tab
                        role="tab"
                        aria-selected="{{ $ocTab === 'register' ? 'true' : 'false' }}">
                    Sign up
                </button>
            </div>

            <div class="auth-oauth-block auth-oauth-block--offcanvas {{ ($uiGoogleSignInAvailable ?? false) ? '' : 'auth-oauth-block--inactive' }}">
                @if($uiGoogleSignInAvailable ?? false)
                    <p class="small text-body-secondary text-center mb-2 mb-md-3">Continue with Google or use your email below.</p>
                    <div class="d-grid">
                        <a href="{{ route('auth.google') }}" class="btn btn-auth-google">
                            <i class="bi bi-google me-2" aria-hidden="true"></i>Google
                        </a>
                    </div>
                @else
                    <p class="small text-body-secondary text-center mb-0 lh-sm">
                        Google sign-in is not configured for this site yet.
                    </p>
                @endif
            </div>

            <div id="oc-auth-panel-login"
                 class="auth-tab-panel {{ $ocTab !== 'login' ? 'd-none' : '' }}"
                 data-auth-panel="login"
                 @if($ocTab !== 'login') hidden @endif>
                <form method="POST" action="{{ route('login') }}" class="auth-form-validate">
                    @csrf
                    <div class="mb-3">
                        <label for="oc-login-email" class="form-label">Email</label>
                        <input id="oc-login-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="oc-login-password" class="form-label">Password</label>
                        <input id="oc-login-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="remember" id="oc-remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="oc-remember">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="small auth-panel-link" href="{{ route('password.request') }}">Forgot?</a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-auth-primary w-100">Sign in</button>
                </form>
            </div>

            <div id="oc-auth-panel-register"
                 class="auth-tab-panel {{ $ocTab !== 'register' ? 'd-none' : '' }}"
                 data-auth-panel="register"
                 @if($ocTab !== 'register') hidden @endif>
                <form method="POST" action="{{ route('register') }}" class="auth-form-validate">
                    @csrf
                    <div class="mb-3">
                        <label for="oc-register-name" class="form-label">Full name</label>
                        <input id="oc-register-name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Your name">
                        @error('name')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="oc-register-email" class="form-label">Email</label>
                        <input id="oc-register-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
                        @error('email')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="oc-register-password" class="form-label">Password</label>
                        <input id="oc-register-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="oc-password-confirm" class="form-label">Confirm password</label>
                        <input id="oc-password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                    </div>
                    <p class="small text-body-secondary mb-3">By signing up you agree to our terms and privacy policy.</p>
                    <button type="submit" class="btn btn-auth-primary w-100">Create account</button>
                </form>
            </div>

            <p class="text-center small text-muted mb-0 mt-3 pt-2 border-top border-opacity-10">
                Selling handmade?
                <a href="{{ route('register.artisan') }}" class="auth-panel-link fw-semibold">Register as artisan</a>
            </p>
        </div>
    </div>
</div>
