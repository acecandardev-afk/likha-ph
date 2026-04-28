@extends('layouts.app')

@section('title', 'Order details')

@section('content')
<div class="container py-2 py-md-3">
    <div class="mb-3">
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to orders</a>
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
                    <div class="d-flex gap-2">
                        <a href="{{ route('customer.orders.tracking', $order) }}" class="btn btn-sm btn-outline-secondary">Track delivery</a>
                        <a href="{{ route('messages.index', $order) }}" class="btn btn-sm btn-outline-primary">Open messages</a>
                    </div>
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
                    @include('partials.order-totals')
                    <p class="small text-muted mb-0">Pay your rider the total shown above when they arrive.</p>
                    <div class="d-flex justify-content-between mb-2 mt-3"><span>Est. delivery window</span><span>{{ $order->estimated_delivery_date }}</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-0"><span>Status</span><x-status-badge :status="$order->status" type="order" /></div>
                    <div class="d-flex justify-content-between align-items-center mt-2"><span>Delivery</span><x-status-badge :status="$order->delivery_status" type="delivery" /></div>
                    @if($order->rider)
                        <p class="small text-muted mt-2 mb-0">Rider: {{ $order->rider->full_name }} ({{ $order->rider->contact_number }})</p>
                    @endif
                    @if($order->isOnDelivery())
                        <div class="mt-3">
                            <form id="form-customer-order-mark-received" method="POST" action="{{ route('customer.orders.mark-received', $order) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalMarkReceivedOrder">
                                    <i class="bi bi-check-circle me-1"></i> I have received the item
                                </button>
                            </form>
                        </div>
                    @endif
                    @can('cancel', $order)
                        <div class="mt-3 pt-3 border-top">
                            <form id="form-customer-order-cancel" method="POST" action="{{ route('customer.orders.cancel', $order) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalCancelOrder">
                                    <i class="bi bi-x-circle me-1"></i> Cancel order
                                </button>
                            </form>
                            @unless(auth()->user()?->isAdmin())
                                <p class="text-muted small mb-0 mt-2">You can cancel only while the order is still pending.</p>
                            @endunless
                        </div>
                    @endcan
                </div>
            </div>

            @if($order->packages->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Delivery packages</h5>
                </div>
                <div class="card-body">
                    @foreach($order->packages as $pkg)
                        <div class="border rounded p-3 mb-3 mb-md-2">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold small">Package {{ $pkg->sequence }}</span>
                                <x-status-badge :status="$pkg->delivery_status" type="delivery" />
                            </div>
                            @if($pkg->rider)
                                <p class="small text-muted mb-1 mb-md-2">Rider: {{ $pkg->rider->full_name }} @if($pkg->rider->contact_number)<span class="text-muted">· {{ $pkg->rider->contact_number }}</span>@endif</p>
                            @endif
                            @if($pkg->delivery_proof_image_url)
                                <p class="small mb-2"><a href="{{ $pkg->delivery_proof_image_url }}" target="_blank" rel="noopener">View delivery photo</a></p>
                            @endif
                            <a href="{{ route('customer.delivery-reports.create', $pkg) }}" class="btn btn-sm btn-outline-secondary">Report an issue with this delivery</a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

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

            <div class="card">
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
        </div>
    </div>

    @if($order->isOnDelivery())
        <x-confirm-form-modal
            id="modalMarkReceivedOrder"
            form-id="form-customer-order-mark-received"
            title="Confirm you received the order"
            message="Only confirm after the items are in your hands. This updates the order and helps sellers get paid on time."
            submit-label="Yes, I received the items"
            submit-variant="success"
            cancel-label="Not yet"
        />
    @endif
    @can('cancel', $order)
        <x-confirm-form-modal
            id="modalCancelOrder"
            form-id="form-customer-order-cancel"
            title="Cancel this order?"
            message="The order will be cancelled. This cannot be undone. If you already paid, follow up in Messages or with support if needed."
            submit-label="Yes, cancel this order"
            submit-variant="danger"
            cancel-label="Keep order"
        />
    @endcan
</div>
@endsection
