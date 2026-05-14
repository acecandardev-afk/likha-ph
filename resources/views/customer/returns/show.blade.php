@extends('layouts.app')

@section('title', 'Return details')

@section('content')
<div class="container py-2 py-md-3" style="max-width: 720px;">
    <x-profile-header-nav active="returns" />
    <a href="{{ route('customer.returns.index') }}" class="btn btn-outline-secondary btn-sm mb-3">All returns</a>
    <a href="{{ route('customer.orders.show', $orderItemReturn->order) }}" class="btn btn-outline-secondary btn-sm mb-3 ms-1">Order</a>

    <h1 class="h5 fw-semibold mb-2">Return request</h1>
    <p class="text-muted small mb-4">Order {{ $orderItemReturn->order?->order_number }} · {{ $orderItemReturn->orderItem?->product_name }}</p>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                <span class="badge bg-secondary">{{ $orderItemReturn->statusLabel() }}</span>
                <span class="text-muted small">{{ $orderItemReturn->created_at?->format('M d, Y H:i') }}</span>
            </div>
            <dl class="row small mb-0">
                <dt class="col-sm-4">Quantity</dt>
                <dd class="col-sm-8">{{ $orderItemReturn->quantity }}</dd>
                <dt class="col-sm-4">Reason</dt>
                <dd class="col-sm-8">{{ $orderItemReturn->reasonLabel() }}</dd>
                <dt class="col-sm-4">Your notes</dt>
                <dd class="col-sm-8" style="white-space: pre-wrap;">{{ $orderItemReturn->notes }}</dd>
            </dl>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header fw-semibold">Photo you submitted</div>
        <div class="card-body text-center">
            <a href="{{ $orderItemReturn->proof_image_url }}" target="_blank" rel="noopener">
                <img src="{{ $orderItemReturn->proof_image_url }}" alt="Return proof" class="img-fluid rounded border" style="max-height: 420px;">
            </a>
        </div>
    </div>

    @if($orderItemReturn->reviewed_at)
        <div class="card border-light">
            <div class="card-body small">
                <div class="fw-semibold mb-1">Admin decision</div>
                @if($orderItemReturn->admin_resolution_notes)
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $orderItemReturn->admin_resolution_notes }}</p>
                @else
                    <p class="text-muted mb-0">No extra notes from admin.</p>
                @endif
                <p class="text-muted mb-0 mt-2">Reviewed {{ $orderItemReturn->reviewed_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    @endif
</div>
@endsection
