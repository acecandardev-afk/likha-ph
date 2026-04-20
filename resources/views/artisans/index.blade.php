@extends('layouts.app')

@section('title', 'Our Artisans')

@section('main_class', 'py-3 py-md-4')

@section('content')
<div class="container-fluid px-3 px-lg-5 artisans-page artisans-page--sub">
    <div class="artisans-hero-sub overflow-hidden reveal">
        <div class="artisans-hero-sub-inner px-3 py-4 py-md-5">
            <p class="artisans-hero-sub-label mb-2">Likha PH</p>
            <h1 class="artisans-hero-sub-title mb-2">Meet our artisans</h1>
            <p class="artisans-hero-sub-text mb-0">The people behind every handcrafted piece. Discover their stories and shop their creations.</p>
        </div>
    </div>

    @if($artisans->isEmpty())
        <div class="text-center py-5 reveal">
            <div class="artisans-empty-icon mb-3">
                <i class="bi bi-people"></i>
            </div>
            <h2 class="h5 fw-semibold mb-2">No artisans yet</h2>
            <p class="text-body-secondary mb-0">Check back soon — we’re always adding new makers.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            @foreach($artisans as $artisan)
                <div class="col reveal">
                    <a href="{{ route('artisans.show', $artisan) }}" class="artisan-card artisan-card-sub card h-100 text-decoration-none border-0 shadow-sm shadow-soft-hover">
                        <div class="artisan-card-image-wrapper position-relative overflow-hidden">
                            @if($artisan->artisanProfile && $artisan->artisanProfile->profile_image_url)
                                <img src="{{ $artisan->artisanProfile->profile_image_url }}" class="artisan-card-img card-img-top" alt="{{ $artisan->artisanProfile->workshop_name }}">
                            @else
                                <div class="artisan-card-placeholder card-img-top d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-badge text-white opacity-75 artisan-placeholder-icon"></i>
                                </div>
                            @endif
                            <span class="artisan-card-badge position-absolute bottom-0 start-0 m-3 px-2 py-1 rounded-pill bg-white shadow-sm small fw-medium">
                                <i class="bi bi-box-seam me-1"></i>{{ $artisan->products_count }} product{{ $artisan->products_count !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <h2 class="h5 fw-semibold text-dark mb-2">{{ $artisan->artisanProfile->workshop_name ?? $artisan->name }}</h2>
                            @if($artisan->artisanProfile && $artisan->artisanProfile->story)
                                <p class="card-text text-body-secondary small mb-3 line-clamp-3">{{ Str::limit($artisan->artisanProfile->story, 120) }}</p>
                            @endif
                            @if($artisan->artisanProfile && $artisan->artisanProfile->full_location)
                                <p class="small text-muted mb-0 d-flex align-items-center">
                                    <i class="bi bi-geo-alt-fill me-1 text-primary"></i>{{ $artisan->artisanProfile->full_location }}
                                </p>
                            @endif
                            <span class="artisan-card-cta d-inline-flex align-items-center mt-3 text-primary fw-medium small">
                                View profile <i class="bi bi-arrow-right ms-1"></i>
                            </span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        @if($artisans->hasPages())
            <div class="d-flex justify-content-center mt-5">{{ $artisans->links() }}</div>
        @endif
    @endif
</div>
@endsection
