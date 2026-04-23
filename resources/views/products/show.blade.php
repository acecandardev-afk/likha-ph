@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div id="productCarousel" class="carousel slide rounded-3 overflow-hidden border" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($product->images as $index => $image)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $image->image_url }}" class="d-block w-100 product-carousel-image" alt="{{ $product->name }}"@if($index === 0) loading="eager" fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif>
                        </div>
                    @endforeach
                </div>
                @if($product->images->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                @endif
            </div>
        </div>

        <div class="col-12 col-md-6">
            <h1 class="h3 fw-semibold mb-2">{{ $product->name }}</h1>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-secondary">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                @if($product->stock > 0)
                    <span class="badge bg-success">In stock ({{ $product->stock }} available)</span>
                @else
                    <span class="badge bg-danger">Out of stock</span>
                @endif
            </div>
            <h2 class="h4 text-primary mb-3">₱{{ number_format($product->price, 2) }}</h2>

            @if($product->average_rating > 0)
                <div class="mb-3">
                    <span class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= $product->average_rating ? '★' : '☆' }}
                        @endfor
                    </span>
                    <span class="text-muted small">({{ $product->total_reviews }} reviews)</span>
                </div>
            @endif

            <p class="text-body-secondary mb-4">{{ $product->description }}</p>

            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title fw-semibold mb-2">Made by</h6>
                    @php($artisan = $product->artisan)
                    <div class="d-flex align-items-center">
                        @if($artisan?->artisanProfile?->profile_image)
                            <img src="{{ $artisan->artisanProfile->profile_image_url }}" class="rounded-circle me-3" width="50" height="50" alt="{{ $artisan?->name ?? 'Unknown Artisan' }}">
                        @endif
                        <div>
                            <strong>{{ $artisan?->name ?? 'Unknown Artisan' }}</strong><br>
                            <small class="text-muted">{{ $artisan?->artisanProfile?->workshop_name ?? '' }}</small><br>
                            @if($artisan)
                                <a href="{{ route('artisans.show', $artisan) }}" class="small">View profile</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add to cart & Buy now: form for logged-in users; login links for guests --}}
            <div class="mb-3">
                @if(auth()->check())
                    @if($product->stock > 0)
                        <form action="{{ route('customer.cart.add', $product) }}" method="POST">
                            @csrf
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <label for="quantity" class="form-label mb-0">Quantity:</label>
                                <input type="number" name="quantity" id="quantity" class="form-control qty-input" value="1" min="1" max="{{ $product->stock }}">
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-outline-primary btn-lg">
                                    <i class="bi bi-cart-plus me-1"></i> Add to cart
                                </button>
                                <button type="submit" name="redirect" value="checkout" class="btn btn-primary btn-lg">
                                    <i class="bi bi-bag-check me-1"></i> Buy now
                                </button>
                            </div>
                        </form>
                    @else
                        <button class="btn btn-outline-primary btn-lg me-2" disabled><i class="bi bi-cart-plus me-1"></i> Add to cart</button>
                        <button class="btn btn-primary btn-lg" disabled><i class="bi bi-bag-check me-1"></i> Buy now</button>
                        <p class="text-muted small mt-2 mb-0">Out of stock. Check back later.</p>
                    @endif
                @else
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('login', ['intended' => url()->current()]) }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-cart-plus me-1"></i> Add to cart
                        </a>
                        <a href="{{ route('login', ['intended' => url()->current()]) }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag-check me-1"></i> Buy now
                        </a>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Log in to purchase.</p>
                @endif
            </div>
        </div>
    </div>

    @if($product->approvedReviews->isNotEmpty())
        <div class="mt-5">
            <h3 class="h5 fw-semibold mb-3">Customer reviews</h3>
            @foreach($product->approvedReviews as $review)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <strong>{{ $review->customer?->name ?? 'Anonymous' }}</strong>
                            <small class="text-muted">{{ $review->created_at?->format('M d, Y') ?? '' }}</small>
                        </div>
                        <div class="text-warning small">
                            @for($i = 1; $i <= 5; $i++)
                                {{ $i <= $review->rating ? '★' : '☆' }}
                            @endfor
                        </div>
                        @if($review->comment)
                            <p class="mt-2 mb-0 small">{{ $review->comment }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($relatedProducts->isNotEmpty())
        <div class="mt-5">
            <h3 class="h5 fw-semibold mb-3">Related products</h3>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-md-4">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col">
                        <x-product-card :product="$relatedProduct" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
