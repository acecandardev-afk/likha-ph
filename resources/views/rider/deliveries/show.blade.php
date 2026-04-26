@extends('layouts.app')

@section('title', 'Delivery progress')

@section('content')
<div class="container py-2 py-md-3">
    <a href="{{ route('rider.deliveries.index') }}" class="btn btn-outline-secondary btn-sm mb-3">Back to deliveries</a>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Order {{ $order->order_number }}</strong>
            <x-status-badge :status="$order->delivery_status" type="delivery" />
        </div>
        <div class="card-body">
            <p class="mb-1"><strong>Customer:</strong> {{ $order->customer?->name }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $order->shipping_phone ?? 'N/A' }}</p>
            <p class="mb-0"><strong>Address:</strong> {{ $order->formattedShippingAddress() }}</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Update delivery progress</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('rider.deliveries.status', $order) }}" enctype="multipart/form-data" class="row g-2">
                @csrf
                @method('PATCH')
                <div class="col-md-4">
                    <select name="delivery_status" class="form-select" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($order->delivery_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5"><input class="form-control" name="note" placeholder="Optional note"></div>
                <div class="col-md-3"><input type="file" name="proof_image" class="form-control" accept="image/*"></div>
                <div class="col-12"><small class="text-muted">Upload proof image when status is Delivered.</small></div>
                <div class="col-12"><button class="btn btn-primary">Save progress</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Delivery timeline</strong></div>
        <div class="card-body">
            @forelse($order->deliveryHistory as $event)
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
