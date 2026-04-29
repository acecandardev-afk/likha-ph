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
            <p class="small text-muted mb-0">Overall status reflects the slowest package still in progress. Each package may have its own rider and delivery photo.</p>
            @if($order->payment && strtolower((string) $order->payment->payment_method) === 'cod')
                <p class="small mb-0 mt-2 alert alert-light border py-2 mb-0"><strong>Cash on delivery:</strong> keep your full order total ready (shown on order details). If there are multiple packages, each rider collects toward that same receipt total until everything is delivered.</p>
            @endif
        </div>
    </div>

    @foreach($order->packages as $pkg)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <strong>Package {{ $pkg->sequence }}</strong>
                <x-status-badge :status="$pkg->delivery_status" type="delivery" />
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Assigned rider</div>
                        <div class="fw-semibold">{{ $pkg->rider?->full_name ?? 'Not assigned yet' }}</div>
                        @if($pkg->rider)
                            <div class="small text-muted">{{ $pkg->rider->contact_number }} @if($pkg->rider->vehicle_type)• {{ $pkg->rider->vehicle_type }}@endif</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Delivery completed</div>
                        <div class="fw-semibold">{{ $pkg->delivery_completed_at?->format('M d, Y h:i A') ?? 'In progress' }}</div>
                    </div>
                </div>
                @if($pkg->delivery_proof_image_url)
                    <div class="mt-3">
                        <div class="small text-muted mb-1">Proof of delivery</div>
                        <img
                            src="{{ $pkg->delivery_proof_image_url }}"
                            alt="Proof of delivery"
                            class="img-fluid rounded border"
                            style="max-width: 260px; max-height: 260px; object-fit: cover;"
                        >
                    </div>
                @endif
                <div class="mt-3">
                    <a href="{{ route('customer.delivery-reports.create', $pkg) }}" class="btn btn-sm btn-outline-secondary">Report an issue</a>
                </div>
            </div>
        </div>
    @endforeach

    <div class="card mb-3">
        <div class="card-header"><strong>Full timeline</strong></div>
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
                <p class="text-muted mb-0">Tracking will appear after rider assignment.</p>
            @endforelse
        </div>
    </div>

    @if($order->delivery_proof_image_url && $order->packages->count() === 1)
    <div class="card">
        <div class="card-header"><strong>Proof of delivery (order)</strong></div>
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
