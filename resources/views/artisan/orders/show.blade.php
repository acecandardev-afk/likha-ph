@extends('layouts.app')

@section('title', 'Order details')

@section('content')
<div class="container py-2 py-md-3">
    <div class="mb-3">
        <a href="{{ route('artisan.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to orders</a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-semibold">Order #{{ $order->order_number }}</h5>
                    <small class="text-muted">Placed on {{ $order->created_at?->format('M d, Y') ?? '' }}</small>
                </div>
                <div class="card-body">
                    @if($order->items->isEmpty())
                        <p class="text-muted mb-0">This order has no items.</p>
                    @else
                        @foreach($order->items as $item)
                            @php $product = $item->product; @endphp
                            <div class="row align-items-center g-2 mb-3 pb-3 border-bottom">
                                <div class="col-3 col-md-2">
                                    @if($product && $product->primaryImage)
                                        <img src="{{ $product->primaryImage->image_url }}" class="img-fluid rounded" alt="{{ $product->name }}">
                                    @endif
                                </div>
                                <div class="col-9 col-md-4">
                                    <h6 class="mb-0">{{ $product->name ?? 'Product not available' }}</h6>
                                    @if($product && $product->category)
                                        <small class="text-muted">{{ $product->category?->name ?? 'Uncategorized' }}</small>
                                    @endif
                                </div>
                                <div class="col-4 col-md-2">₱{{ number_format($item->price, 2) }}</div>
                                <div class="col-4 col-md-2">× {{ $item->quantity }}</div>
                                <div class="col-4 col-md-2 text-end fw-semibold">₱{{ number_format($item->price * $item->quantity, 2) }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Messages</h5>
                    <a href="{{ route('messages.index', $order) }}" class="btn btn-sm btn-outline-primary">Open messages</a>
                </div>
                <div class="card-body">
                    @if($order->messages->isEmpty())
                        <p class="text-muted mb-0 small">No messages yet for this order.</p>
                    @else
                        @foreach($order->messages->take(3) as $message)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="small text-muted">{{ $message->sender?->name ?? 'Unknown Sender' }} · {{ $message->created_at?->diffForHumans() ?? '' }}</div>
                                <div class="mt-1">{{ $message->message }}</div>
                            </div>
                        @endforeach
                        @if($order->messages->count() > 3)
                            <a href="{{ route('messages.index', $order) }}" class="small">View all {{ $order->messages->count() }} messages</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Order summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>₱{{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Platform fee</span><span>₱{{ number_format($order->platform_fee ?? 0, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Total</span><span class="fw-semibold">₱{{ number_format($order->total, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Est. delivery</span><span>{{ $order->estimated_delivery_date }}</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-0"><span>Status</span><x-status-badge :status="$order->status" type="order" /></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Customer</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1 small"><strong>{{ $order->customer?->name ?? 'Unknown Customer' }}</strong></p>
                    <p class="mb-0 small text-muted">{{ $order->customer?->email ?? '' }}</p>
                    @if($order->customer->phone)
                        <p class="mb-0 small">{{ $order->customer->phone }}</p>
                    @endif
                </div>
            </div>

            @if($order->country || $order->region || $order->province || $order->city || $order->barangay || $order->street_address || $order->shipping_phone)
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Delivery address</h5>
                </div>
                <div class="card-body">
                    @if(trim($order->formattedShippingAddress()))
                        <p class="mb-1 small" style="white-space: pre-line;">{{ $order->formattedShippingAddress() }}</p>
                    @endif
                    @if($order->shipping_phone)<p class="mb-0 small"><strong>Contact:</strong> {{ $order->shipping_phone }}</p>@endif
                </div>
            </div>
            @endif

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Payment</h5>
                </div>
                <div class="card-body">
                    @if(!$order->payment)
                        <p class="text-muted mb-0 small">No payment recorded yet.</p>
                    @else
                        <p class="mb-1 small"><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment->payment_method)) }}</p>
                        <p class="mb-1 small"><strong>Amount:</strong> ₱{{ number_format($order->payment->amount, 2) }}</p>
                        <p class="mb-1 small"><strong>Status:</strong> <x-status-badge :status="$order->payment->verification_status" type="payment" /></p>
                        @if($order->payment->proof_image)
                            <p class="mb-0 small"><strong>Proof:</strong> Uploaded</p>
                        @endif
                    @endif
                </div>
            </div>

            @if($order->canBeCompleted())
            <form action="{{ route('artisan.orders.complete', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success w-100">Mark as completed</button>
            </form>
            @endif
            @if($order->canBeApproved())
            <form action="{{ route('artisan.orders.approve', $order) }}" method="POST" class="mt-2">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary w-100">Approve order</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
