@extends('layouts.app')

@section('title', 'Order tracking')

@section('content')
<div class="container py-2 py-md-3">
    <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm mb-3">Back to order details</a>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Tracking for Order {{ $order->order_number }}</strong>
            <x-status-badge :status="$order->delivery_status" type="delivery" />
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Assigned rider</div>
                    <div class="fw-semibold">{{ $order->rider?->full_name ?? 'Not assigned yet' }}</div>
                    @if($order->rider)
                        <div class="small text-muted">{{ $order->rider->contact_number }} • {{ $order->rider->vehicle_type }}</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Delivery completed</div>
                    <div class="fw-semibold">{{ $order->delivery_completed_at?->format('M d, Y h:i A') ?? 'In progress' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Delivery Timeline</strong></div>
        <div class="card-body">
            @forelse($order->deliveryHistory as $event)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <x-status-badge :status="$event->status" type="delivery" />
                        <small class="text-muted">{{ $event->status_at?->format('M d, Y h:i A') }}</small>
                    </div>
                    @if($event->note)<div class="small mt-1">{{ $event->note }}</div>@endif
                </div>
            @empty
                <p class="text-muted mb-0">Tracking will appear after payment verification and rider assignment.</p>
            @endforelse
        </div>
    </div>

    @if($order->delivery_proof_image_url)
    <div class="card">
        <div class="card-header"><strong>Proof of Delivery</strong></div>
        <div class="card-body">
            <img
                src="{{ $order->delivery_proof_image_url }}"
                alt="Proof of delivery"
                class="img-fluid rounded border"
                style="max-width: 260px; max-height: 260px; object-fit: cover;"
            >
        </div>
    </div>
    @endif
</div>
@endsection
