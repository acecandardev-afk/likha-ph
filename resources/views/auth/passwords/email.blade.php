@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <div class="card auth-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 fw-semibold mb-2">Reset your password</h1>
                        <p class="text-body-secondary small mb-0">Enter your email and we’ll send you a link to reset it.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success small mb-4" role="alert">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="you@example.com">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Send password reset link</button>
                    </form>

                    <p class="text-center text-body-secondary small mt-4 mb-0">
                        <a class="text-decoration-none" href="{{ route('login') }}">Back to log in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
