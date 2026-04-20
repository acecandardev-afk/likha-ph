@extends('layouts.app')

@section('title', 'New Sale')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
            <li class="breadcrumb-item active" aria-current="page">New sale</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Record sale</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.sales.store') }}" method="POST" id="saleForm">
                @csrf

                <div id="saleItems">
                    <div class="row g-2 align-items-end sale-item mb-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label small">Product</label>
                            <select name="items[0][product_id]" class="form-select product-select" required>
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                                        {{ $product->name }} (₱{{ number_format($product->price, 2) }}, stock: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small">Qty</label>
                            <input type="number" min="1" name="items[0][quantity]" class="form-control quantity-input" value="1" required>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small">Subtotal</label>
                            <input type="text" class="form-control subtotal-display" value="₱0.00" readonly>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100 remove-item" disabled>Remove</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addItemBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add item
                    </button>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="payment_method" class="form-label small">Payment method</label>
                        <select id="payment_method" name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="amount_paid" class="form-label small">Amount paid</label>
                        <input id="amount_paid" type="number" step="0.01" min="0" name="amount_paid" class="form-control" value="{{ old('amount_paid', 0) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small">Total</label>
                        <input id="totalAmountDisplay" type="text" class="form-control fw-semibold" value="₱0.00" readonly>
                    </div>
                </div>

                @if($errors->has('items'))
                    <div class="alert alert-danger py-2 mt-3 mb-0">{{ $errors->first('items') }}</div>
                @endif

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Record sale</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
