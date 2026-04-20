@extends('layouts.app')

@section('title', 'Review payment – Order ' . (optional($payment->order)->order_number ?? '-'))

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payments.pending') }}">Pending payments</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order {{ $payment->order?->order_number ?? '-' }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">Order {{ $payment->order?->order_number ?? '-' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Customer</div>
                        <div class="col-7">{{ $payment->order?->customer?->name ?? 'Unknown Customer' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Artisan</div>
                        <div class="col-7">{{ $payment->order?->artisan?->artisanProfile?->workshop_name ?? $payment->order?->artisan?->name ?? 'Unknown Artisan' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Amount</div>
                        <div class="col-7 fw-semibold">₱{{ number_format($payment->amount, 2) }}</div>
                    </div>
                    @if($payment->order)
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Subtotal</div>
                            <div class="col-7">₱{{ number_format($payment->order->subtotal ?? 0, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 text-muted">Platform fee</div>
                            <div class="col-7">₱{{ number_format($payment->order->platform_fee ?? 0, 2) }}</div>
                        </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-5 text-muted">Payment method</div>
                        <div class="col-7">{{ ucfirst($payment->payment_method ?? 'N/A') }}</div>
                    </div>
                    <hr>
                    <h6 class="fw-semibold mb-2">Items</h6>
                    @foreach(($payment->order?->items ?? collect()) as $item)
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                            <span>{{ $item->product->name ?? 'Product' }} × {{ $item->quantity }}</span>
                            <span>₱{{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">Payment proof</h5>
                </div>
                <div class="card-body text-center">
                    @if($payment->proof_image)
                        <a href="{{ $payment->proof_image_url }}" target="_blank" class="d-inline-block">
                            <img src="{{ $payment->proof_image_url }}" alt="Payment proof" class="img-fluid rounded border" style="max-height: 320px;">
                        </a>
                        <p class="small text-muted mt-2 mb-0">Click to open full size</p>
                    @else
                        <p class="text-muted mb-0">No proof image uploaded.</p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">Verification decision</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="verify_notes" class="form-label small">Notes (optional)</label>
                        <textarea form="verifyForm" name="notes" id="verify_notes" rows="2" class="form-control form-control-sm" placeholder="Optional verification notes">{{ old('notes') }}</textarea>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <form id="verifyForm" action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Verify
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                        <a href="{{ route('admin.payments.pending') }}" class="btn btn-outline-secondary">Back to pending</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject payment modal --}}
<div class="modal fade" id="rejectPaymentModal" tabindex="-1" aria-labelledby="rejectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="rejectPaymentModalLabel">Reject payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <p class="text-muted mb-3">Reject payment for order {{ $payment->order?->order_number ?? '-' }}? Please provide a reason. Stock will be restored for the order items.</p>
                <form action="{{ route('admin.payments.reject', $payment) }}" method="POST" id="rejectPaymentForm">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label fw-medium">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reject_reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="Reason for rejection..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Reject payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->has('reason'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('rejectPaymentModal'));
            modal.show();
        });
    </script>
@endif
@endsection
