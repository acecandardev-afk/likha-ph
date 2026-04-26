@extends('layouts.app')

@section('title', 'Home')

@section('main_class', 'pt-0 pb-0')

@section('content')
<div class="home-storefront home-storefront--neo">
    <section class="lk-min-hero" aria-labelledby="home-hero-heading">
        <div class="container-fluid px-3 px-lg-5">
            <div class="lk-min-hero__inner">
                <div class="lk-min-hero__copy">
                    <h1 id="home-hero-heading" class="lk-min-hero__title">Handmade products from local makers</h1>
                    <p class="lk-min-hero__text">Shop trusted products, chat with sellers, and track your delivery in one place.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-dark" href="{{ route('products.index') }}">Shop now</a>
                        <a class="btn btn-outline-dark" href="{{ route('artisans.index') }}">Meet artisans</a>
                    </div>
                </div>
                <div class="lk-min-hero__media">
                    @if($heroImageUrl)
                        <img src="{{ $heroImageUrl }}" alt="Featured product" class="lk-min-hero__img" width="860" height="520" loading="eager">
                    @else
                        <div class="lk-min-hero__placeholder">Featured product image</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="home-stats-strip home-stats-strip--neo" aria-label="Marketplace highlights">
        <div class="container-fluid px-3 px-lg-5">
            <div class="row g-0 home-stats-strip__grid">
                <div class="col-6 col-lg-3 home-stats-strip__cell">
                    <div class="home-stats-strip__inner home-stats-strip__inner--text">
                        <span class="home-stats-strip__num">{{ number_format($stats['products']) }}</span>
                        <span class="home-stats-strip__label">Products listed</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3 home-stats-strip__cell">
                    <div class="home-stats-strip__inner home-stats-strip__inner--text">
                        <span class="home-stats-strip__num">{{ number_format($stats['artisans']) }}</span>
                        <span class="home-stats-strip__label">Artisans</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3 home-stats-strip__cell">
                    <div class="home-stats-strip__inner home-stats-strip__inner--text">
                        <span class="home-stats-strip__num">{{ number_format($stats['categories']) }}</span>
                        <span class="home-stats-strip__label">Categories</span>
                    </div>
                </div>
                <div class="col-6 col-lg-3 home-stats-strip__cell">
                    <div class="home-stats-strip__inner home-stats-strip__inner--text">
                        <span class="home-stats-strip__num">PH</span>
                        <span class="home-stats-strip__label">Based in Guihulngan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section home-section--neo-muted" aria-labelledby="home-why-heading">
        <div class="container-fluid px-3 px-lg-5">
            <div class="home-section-head text-center mx-auto mb-4 mb-lg-5">
                <h2 id="home-why-heading" class="home-section-title home-section-title--serif mb-2">Why Likha PH</h2>
                <p class="home-section-sub text-muted mb-0">A simple marketplace for handmade products.</p>
            </div>
            <div class="row g-3 g-md-4 justify-content-center">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="home-feature-card home-feature-card--neo h-100">
                        <h3 class="home-feature-card__title">Handmade products</h3>
                        <p class="home-feature-card__text mb-0">Each listing comes from local artisans.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="home-feature-card home-feature-card--neo h-100">
                        <h3 class="home-feature-card__title">Clear pricing</h3>
                        <p class="home-feature-card__text mb-0">Product prices and stock are shown clearly.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="home-feature-card home-feature-card--neo h-100">
                        <h3 class="home-feature-card__title">Artisan profiles</h3>
                        <p class="home-feature-card__text mb-0">View information about sellers before buying.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="home-feature-card home-feature-card--neo h-100">
                        <h3 class="home-feature-card__title">Simple checkout</h3>
                        <p class="home-feature-card__text mb-0">Add items, place orders, and track status.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section" aria-labelledby="home-featured-heading">
        <div class="container-fluid px-3 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 mb-lg-4">
                <div>
                    <h2 id="home-featured-heading" class="home-section-title home-section-title--serif mb-1">Featured products</h2>
                    <p class="text-muted mb-0 small">Newest approved items from our sellers.</p>
                </div>
                <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm px-4">See all products</a>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-lg-4 align-items-stretch">
                @forelse($featuredProducts as $product)
                    <div class="col d-flex">
                        <div class="w-100">
                            <x-product-card :product="$product" />
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="home-empty-panel home-empty-panel--neo">
                            <h3 class="h5 mb-2">No products yet</h3>
                            <p class="text-muted mb-3 mb-md-4">Please check back later.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-dark">Browse products</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="home-section home-section--tight-top" aria-labelledby="home-categories-heading">
        <div class="container-fluid px-3 px-lg-5">
            <div class="home-section-head mb-4 mb-lg-5">
                <h2 id="home-categories-heading" class="home-section-title home-section-title--serif mb-2">Shop by category</h2>
                <p class="text-muted mb-0">Choose a category to filter products.</p>
            </div>

            @if($categories->isEmpty())
                <div class="home-empty-panel home-empty-panel--compact home-empty-panel--neo">
                    <p class="text-muted mb-2 mb-md-0">Categories will appear when available.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-dark btn-sm">Browse all</a>
                </div>
            @else
                <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-6 g-2 g-sm-3">
                    @foreach($categories as $category)
                        <div class="col d-flex">
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-decoration-none w-100 home-category-link">
                                <div class="home-category-tile home-category-tile--neo h-100">
                                    <span class="home-category-tile__name">{{ $category->name }}</span>
                                    <span class="home-category-tile__count">{{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="home-section home-section--neo-muted" aria-labelledby="home-artisans-heading">
        <div class="container-fluid px-3 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3 mb-4 mb-lg-4">
                <div>
                    <h2 id="home-artisans-heading" class="home-section-title home-section-title--serif mb-1">Artisans</h2>
                    <p class="text-muted mb-0 small">Get to know the people behind each product.</p>
                </div>
                <a href="{{ route('artisans.index') }}" class="btn btn-outline-dark btn-sm px-4">See all artisans</a>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-lg-4 align-items-stretch">
                @forelse($featuredArtisans as $artisan)
                    @php
                        $profile = $artisan->artisanProfile;
                        $workshopName = $profile->workshop_name ?? $artisan->name;
                    @endphp
                    <div class="col d-flex">
                        <a href="{{ route('artisans.show', $artisan) }}" class="text-decoration-none text-reset d-flex w-100 home-artisan-card-link">
                            <article class="card h-100 w-100 artisan-card home-artisan-card home-artisan-card--neo border-0">
                                <div class="home-artisan-card__media">
                                    @if($profile && $profile->profile_image)
                                        <img src="{{ $profile->profile_image_url }}"
                                             class="home-artisan-card__img"
                                             alt="{{ $workshopName }}"
                                             loading="lazy">
                                    @else
                                        <div class="home-artisan-card__placeholder"></div>
                                    @endif
                                </div>
                                <div class="card-body d-flex flex-column pt-3">
                                    <h3 class="h6 card-title fw-bold mb-1">{{ $workshopName }}</h3>
                                    <p class="text-muted small mb-2">by {{ $artisan->name }}</p>
                                    <p class="card-text small text-body-secondary flex-grow-1 mb-3">{{ Str::limit($profile->story ?? 'Handmade products from local artisans.', 100) }}</p>
                                    <div class="d-flex align-items-center justify-content-between mt-auto">
                                        <span class="badge rounded-0 bg-dark text-white border border-2 border-dark">{{ $artisan->products_count }} {{ Str::plural('product', $artisan->products_count) }}</span>
                                        <span class="small fw-bold text-uppercase letter-spacing-sm">View</span>
                                    </div>
                                </div>
                            </article>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="home-empty-panel home-empty-panel--neo">
                            <h3 class="h5 mb-2">No artisan profiles yet</h3>
                            <p class="text-muted mb-0">Profiles will appear here when available.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-dark mt-3">Browse products</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="home-cta-band home-cta-band--neo" aria-labelledby="home-cta-heading">
        <div class="container-fluid px-3 px-lg-5 py-5 py-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h2 id="home-cta-heading" class="home-cta-band__title mb-2">Start shopping</h2>
                    <p class="home-cta-band__text mb-0">Create an account to place orders and track deliveries.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row flex-lg-column flex-xl-row gap-2 justify-content-lg-end">
                        @guest
                            <button type="button" class="btn btn-light btn-lg" data-bs-toggle="offcanvas" data-bs-target="#likhaAuthPanel" data-auth-tab="register">Sign up</button>
                            <button type="button" class="btn btn-outline-light btn-lg" data-bs-toggle="offcanvas" data-bs-target="#likhaAuthPanel" data-auth-tab="login">Log in</button>
                        @else
                            <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">Continue shopping</a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
