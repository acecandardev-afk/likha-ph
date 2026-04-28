@extends('layouts.app')

@section('title', 'Delivery progress')

@section('content')
<div class="container py-2 py-md-3">
    <a href="{{ route('rider.deliveries.index') }}" class="btn btn-outline-secondary btn-sm mb-3">Back to deliveries</a>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>Order {{ $order->order_number }} · Package {{ $orderPackage->sequence }}</strong>
            <x-status-badge :status="$orderPackage->delivery_status" type="delivery" />
        </div>
        <div class="card-body">
            <p class="mb-1"><strong>Customer:</strong> {{ $order->customer?->name }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $order->shipping_phone ?? 'N/A' }}</p>
            <p class="mb-0"><strong>Address:</strong> {{ $order->formattedShippingAddress() }}</p>
        </div>
    </div>

    @if($orderPackage->items->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header"><strong>Items in this package</strong></div>
        <div class="card-body py-2">
            @foreach($orderPackage->items as $pi)
                @php $oi = $pi->orderItem; @endphp
                <div class="small py-1 border-bottom">{{ $oi->product_name ?? 'Item' }} × {{ $pi->quantity }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Update delivery progress</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('rider.deliveries.status', $orderPackage) }}" enctype="multipart/form-data" class="row g-2">
                @csrf
                @method('PATCH')
                <div class="col-md-4">
                    <select name="delivery_status" class="form-select" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($orderPackage->delivery_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5"><input class="form-control" name="note" placeholder="Optional note"></div>
                <div class="col-md-3"><input type="file" name="proof_image" class="form-control" accept="image/*"></div>
                <div class="col-12"><small class="text-muted">Upload a photo when status is Delivered (proof of handoff).</small></div>
                <div class="col-12"><button class="btn btn-primary">Save progress</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Timeline</strong></div>
        <div class="card-body">
            @php
                $events = $order->deliveryHistory->filter(function ($e) use ($orderPackage) {
                    return $e->order_package_id === null || (int) $e->order_package_id === (int) $orderPackage->id;
                });
            @endphp
            @forelse($events as $event)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div><x-status-badge :status="$event->status" type="delivery" /></div>
                        <small class="text-muted">{{ $event->status_at?->format('M d, Y h:i A') }}</small>
                    </div>
                    @if($event->note)<div class="small mt-1">{{ $event->note }}</div>@endif
                    <small class="text-muted">Updated by: {{ $event->actor?->name ?? 'System' }}</small>
                </div>
            @empty
                <p class="text-muted mb-0">No tracking entries yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
