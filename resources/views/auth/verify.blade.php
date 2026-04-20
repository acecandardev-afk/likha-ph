@extends('layouts.app')

@section('title', 'Verify email')

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <div class="card auth-card border-0">
                <div class="card-body p-4 p-md-5 text-center">
                    <h1 class="h3 fw-semibold mb-2">Verify your email</h1>
                    <p class="text-body-secondary small mb-4">Before continuing, please check your email for a verification link.</p>

                    @if (session('resent'))
                        <div class="alert alert-success small mb-4" role="alert">A new verification link has been sent to your email.</div>
                    @endif

                    <p class="small mb-4">If you didn’t receive the email, we can send another.</p>
                    <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg">Send another link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
