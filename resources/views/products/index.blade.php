@extends('layouts.app')

@section('title', 'Products')

@section('main_class', 'py-0')

@section('content')
<div class="shop-page">
    <header class="shop-page__hero reveal">
        <div class="container-fluid px-3 px-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-md-between gap-3">
                <div>
                    <h1 class="shop-page__title mb-1">Shop</h1>
                    <p class="text-muted small mb-0">Handmade pieces—filter by category, search, or sort.</p>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-3 px-lg-5 pb-4 pb-md-5">
        <div class="row g-4">
            <div class="col-12 col-lg-3">
                <div class="card shop-filter-card mb-4 mb-lg-0 reveal">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h2 class="h6 fw-bold mb-0 text-uppercase letter-spacing-sm">Categories</h2>
                    </div>
                    <div class="list-group list-group-flush rounded-0 border-0 px-2 pb-2">
                        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action border-0 rounded-3 {{ !request('category') ? 'active' : '' }}">All products</a>
                        @foreach($categories as $category)
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="list-group-item list-group-item-action border-0 rounded-3 {{ request('category') === $category->slug ? 'active' : '' }}">{{ $category->icon }} {{ $category->name }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="card shop-filter-card reveal">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h2 class="h6 fw-bold mb-0 text-uppercase letter-spacing-sm">Search</h2>
                    </div>
                    <div class="card-body pt-2">
                        <form action="{{ route('products.index') }}" method="GET">
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control rounded-pill rounded-end-0" placeholder="Search products…" value="{{ request('search') }}">
                                <button class="btn btn-outline-dark rounded-pill rounded-start-0" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 reveal">
                    <p class="small text-muted mb-0">{{ $products->total() }} {{ Str::plural('result', $products->total()) }}</p>
                    <form action="{{ route('products.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                        <label for="shop-sort" class="small text-muted mb-0 d-none d-sm-inline">Sort</label>
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <select id="shop-sort" name="sort" class="form-select form-select-sm shop-sort-select" onchange="this.form.submit()">
                            <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: low to high</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: high to low</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name: A–Z</option>
                        </select>
                    </form>
                </div>

                @if($products->isEmpty())
                    <div class="alert alert-info border-0 rounded-3 shadow-sm reveal">No products match your criteria. Try another category or search.</div>
                @else
                    @auth
                        @if(auth()->user()->isCustomer())
                            <p class="text-muted small mb-3 reveal">Use <strong>Add to cart</strong> to keep shopping. <strong>Buy now</strong> opens the product page so you can set quantity, then continue to checkout.</p>
                        @endif
                    @endauth
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-3 g-md-4">
                        @foreach($products as $product)
                            <div class="col d-flex reveal">
                                <div class="product-card-wrap w-100">
                                    <x-product-card :product="$product" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 d-flex justify-content-center">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
