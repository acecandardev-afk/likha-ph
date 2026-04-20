@extends('layouts.app')

@section('title', 'Application pending')

@section('content')
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-soft p-0 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    @php($status = auth()->user()->status ?? 'pending')
                    @if($status === 'active')
                        <h1 class="h3 fw-semibold mb-2">Your artisan account is approved</h1>
                        <p class="text-muted mb-4">
                            You can now start listing your products for review.
                        </p>
                    @elseif($status === 'suspended')
                        <h1 class="h3 fw-semibold mb-2">Unfortunately, your application was rejected</h1>
                        <p class="text-muted mb-4">
                            Please update your details and apply again later.
                        </p>
                    @else
                        <h1 class="h3 fw-semibold mb-2">Your request is now being reviewed</h1>
                        <p class="text-muted mb-4">
                            Please wait for our email confirmation. You will be able to start selling once your artisan account is approved.
                        </p>
                    @endif

                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('home') }}" class="btn btn-outline-dark w-100">Go to home</a>
                        @if(($status ?? null) === 'active')
                            <a href="{{ route('artisan.products.index') }}" class="btn btn-primary w-100">View products</a>
                        @else
                            <a href="{{ route('products.index') }}" class="btn btn-primary w-100">Browse products</a>
                        @endif
                    </div>

                    <div class="small text-muted mt-3">
                        Tip: check your inbox and spam folder for messages from Likha PH.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

