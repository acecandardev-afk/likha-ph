@extends('layouts.app')

@section('title', 'Shopping cart')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cart</li>
        </ol>
    </nav>
    <h2 class="h4 fw-semibold mb-2">Shopping cart</h2>
    <p class="text-muted small mb-4">Review your items, update quantity if needed, then proceed to checkout.</p>

    @if($cartItems->isEmpty())
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-cart-x me-2 fs-4"></i>
            <span>Your cart is empty. <a href="{{ route('products.index') }}" class="alert-link">Browse products</a></span>
        </div>
    @else
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                @foreach($groupedByArtisan as $artisanId => $items)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 fw-semibold">
                                <i class="bi bi-person-badge me-1"></i>
                                        {{ $items->first()->product->artisan?->artisanProfile?->workshop_name ?? $items->first()->product->artisan?->name ?? 'Unknown Artisan' }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($items as $item)
                                <div class="row align-items-center g-2 p-3 border-bottom">
                                    <div class="col-4 col-md-2">
                                        @if($item->product->primaryImage)
                                            <img src="{{ $item->product->primaryImage->image_url }}" alt="{{ $item->product->name }}" class="img-fluid rounded" loading="lazy" decoding="async">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 70px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-8 col-md-4">
                                        <h6 class="mb-0">
                                            <a href="{{ route('products.show', $item->product) }}" class="text-decoration-none">{{ $item->product->name }}</a>
                                        </h6>
                                    <small class="text-muted">{{ $item->product->category?->name ?? 'Uncategorized' }}</small>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <span class="fw-semibold">₱{{ number_format($item->product->price, 2) }}</span>
                                    </div>
                                    <div class="col-4 col-md-2">
                                        <form action="{{ route('customer.cart.update', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" class="form-control form-control-sm" onchange="this.form.submit()">
                                        </form>
                                    </div>
                                    <div class="col-6 col-md-2 text-end">
                                        <span class="fw-semibold d-block mb-1">₱{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#removeItemModal"
                                                data-item-name="{{ $item->product->name }}"
                                                data-remove-url="{{ route('customer.cart.remove', $item) }}">
                                            <i class="bi bi-trash me-1"></i>
                                            <span class="d-none d-sm-inline">Remove</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                    <button type="button"
                            class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#clearCartModal">
                        <i class="bi bi-cart-x me-1"></i>
                        Clear cart
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Continue shopping</a>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0 fw-semibold">Order summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span class="fw-semibold">₱{{ number_format($total, 2) }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span class="text-muted small">Calculated at checkout</span></div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3"><span class="fw-semibold">Total</span><span class="fw-bold fs-5">₱{{ number_format($total, 2) }}</span></div>
                        <a href="{{ route('customer.checkout.index') }}" class="btn btn-primary w-100 btn-lg"><i class="bi bi-bag-check me-1"></i> Proceed to checkout</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Remove Item Confirmation Modal --}}
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5 class="modal-title fw-semibold mb-2" id="removeItemModalLabel">Remove item from cart?</h5>
                <p class="text-muted mb-4" id="removeItemName">Are you sure you want to remove this item from your cart?</p>
                <form id="removeItemForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Remove
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Clear Cart Confirmation Modal --}}
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-labelledby="clearCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div class="mb-3">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5 class="modal-title fw-semibold mb-2" id="clearCartModalLabel">Clear your entire cart?</h5>
                <p class="text-muted mb-4">This will remove all items from your cart. This action cannot be undone.</p>
                <form action="{{ route('customer.cart.clear') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-cart-x me-1"></i> Clear cart
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handle remove item modal
    const removeItemModal = document.getElementById('removeItemModal');
    if (removeItemModal) {
        removeItemModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemName = button.getAttribute('data-item-name');
            const removeUrl = button.getAttribute('data-remove-url');
            
            const modalTitle = removeItemModal.querySelector('#removeItemModalLabel');
            const modalBody = removeItemModal.querySelector('#removeItemName');
            const form = removeItemModal.querySelector('#removeItemForm');
            
            modalTitle.textContent = 'Remove item from cart?';
            modalBody.textContent = `Are you sure you want to remove "${itemName}" from your cart?`;
            form.action = removeUrl;
        });
    }
</script>
@endpush
@endsection
