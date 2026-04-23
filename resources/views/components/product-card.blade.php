@props(['product'])

<div class="card h-100 product-card product-card--sub shadow-soft-hover">
    <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
        @if($product->primaryImage)
            <img src="{{ $product->primaryImage->image_url }}" class="card-img-top product-card-img" alt="{{ $product->name }}" loading="lazy" decoding="async">
        @else
            <div class="card-img-top product-card-img product-card-placeholder d-flex align-items-center justify-content-center">
                <span class="text-muted small">No image</span>
            </div>
        @endif
    </a>
    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-1">
            <a href="{{ route('products.show', $product) }}" class="text-dark text-decoration-none">{{ Str::limit($product->name, 45) }}</a>
        </h5>
        @php($artisan = $product->artisan)
        <p class="card-text text-muted small mb-2">
            by
            @if($artisan)
                <a href="{{ route('artisans.show', $artisan) }}" class="text-decoration-none">{{ $artisan->artisanProfile?->workshop_name ?? $artisan->name }}</a>
            @else
                <span class="text-muted">Unknown Artisan</span>
            @endif
        </p>
        <p class="card-text small mb-2 flex-grow-1 text-body-secondary">{{ Str::limit($product->description, 80) }}</p>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
            <span class="h6 mb-0 text-primary">₱{{ number_format($product->price, 2) }}</span>
            @if($product->stock > 0)
                <span class="badge bg-success">In stock</span>
            @else
                <span class="badge bg-danger">Out of stock</span>
            @endif
        </div>
        @if($product->average_rating > 0)
            <div class="mt-2 small">
                <span class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        {{ $i <= $product->average_rating ? '★' : '☆' }}
                    @endfor
                </span>
                <span class="text-muted">({{ $product->total_reviews }})</span>
            </div>
        @endif

        {{-- Add to cart & Buy now: form for logged-in users; login links for guests --}}
        <div class="mt-3 pt-2 border-top">
            @if(auth()->check())
                @if($product->stock > 0)
                    <form action="{{ route('customer.cart.add', $product) }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill">
                            <i class="bi bi-cart-plus me-1"></i> Add to cart
                        </button>
                        <button type="submit" name="redirect" value="checkout" class="btn btn-sm btn-primary flex-grow-1 rounded-pill">
                            <i class="bi bi-bag-check me-1"></i> Buy now
                        </button>
                    </form>
                @else
                    <button class="btn btn-sm btn-outline-primary flex-grow-1" disabled><i class="bi bi-cart-plus me-1"></i> Add to cart</button>
                    <button class="btn btn-sm btn-primary flex-grow-1" disabled><i class="bi bi-bag-check me-1"></i> Buy now</button>
                @endif
            @else
                <a href="{{ route('login', ['intended' => route('products.show', $product)]) }}" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill"><i class="bi bi-cart-plus me-1"></i> Add to cart</a>
                <a href="{{ route('login', ['intended' => route('products.show', $product)]) }}" class="btn btn-sm btn-primary flex-grow-1 rounded-pill"><i class="bi bi-bag-check me-1"></i> Buy now</a>
            @endif
        </div>
    </div>
</div>
