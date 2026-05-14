@extends('layouts.app')

@section('title', 'Review return')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.order-returns.index') }}">Item returns</a></li>
            <li class="breadcrumb-item active">#{{ $orderItemReturn->id }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Buyer proof photo</div>
                <div class="card-body text-center bg-light">
                    <a href="{{ $orderItemReturn->proof_image_url }}" target="_blank" rel="noopener">
                        <img src="{{ $orderItemReturn->proof_image_url }}" alt="Proof" class="img-fluid rounded border" style="max-height: 480px;">
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Request</div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Order</strong> {{ $orderItemReturn->order?->order_number }}</p>
                    <p class="mb-1"><strong>Item</strong> {{ $orderItemReturn->orderItem?->product_name }}</p>
                    <p class="mb-1"><strong>Qty</strong> {{ $orderItemReturn->quantity }}</p>
                    <p class="mb-1"><strong>Reason</strong> {{ $orderItemReturn->reasonLabel() }}</p>
                    <p class="mb-1"><strong>Buyer</strong> {{ $orderItemReturn->customer?->name }} ({{ $orderItemReturn->customer?->email }})</p>
                    <p class="mb-0"><strong>Seller</strong> {{ $orderItemReturn->artisan?->name }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Buyer notes</div>
                <div class="card-body small" style="white-space: pre-wrap;">{{ $orderItemReturn->notes }}</div>
            </div>

            @if($orderItemReturn->status === \App\Models\OrderItemReturn::STATUS_PENDING_ADMIN)
                <div class="card border-success mb-3">
                    <div class="card-header bg-success bg-opacity-10 fw-semibold text-success">Approve</div>
                    <div class="card-body">
                        <p class="small text-muted">Approving will add the returned quantity back to product stock (when the product still exists).</p>
                        <form method="POST" action="{{ route('admin.order-returns.approve', $orderItemReturn) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <label class="form-label small">Admin notes (optional)</label>
                                <textarea name="admin_resolution_notes" class="form-control form-control-sm" rows="2" maxlength="5000" placeholder="Message to buyer / seller">{{ old('admin_resolution_notes') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Approve return</button>
                        </form>
                    </div>
                </div>
                <div class="card border-danger">
                    <div class="card-header bg-danger bg-opacity-10 fw-semibold text-danger">Reject</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.order-returns.reject', $orderItemReturn) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <label class="form-label small">Reason for rejection (recommended)</label>
                                <textarea name="admin_resolution_notes" class="form-control form-control-sm" rows="3" maxlength="5000" placeholder="Explain why this return cannot be approved">{{ old('admin_resolution_notes') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100">Reject return</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-secondary mb-0">
                    <div class="fw-semibold mb-1">Status: {{ $orderItemReturn->statusLabel() }}</div>
                    @if($orderItemReturn->admin_resolution_notes)
                        <div class="small" style="white-space: pre-wrap;">{{ $orderItemReturn->admin_resolution_notes }}</div>
                    @endif
                    <div class="small text-muted mt-2">Processed {{ $orderItemReturn->reviewed_at?->format('M d, Y H:i') }} @if($orderItemReturn->reviewer)by {{ $orderItemReturn->reviewer->name }}@endif</div>
                    @if($orderItemReturn->stock_restored_at)
                        <div class="small text-success mt-2">Stock restored at {{ $orderItemReturn->stock_restored_at->format('M d, Y H:i') }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
