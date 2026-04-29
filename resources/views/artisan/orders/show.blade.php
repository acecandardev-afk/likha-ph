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
                    @include('partials.order-totals')
                    <div class="d-flex justify-content-between mb-1 mt-2"><span>Your estimated share from items</span><span class="fw-semibold">₱{{ number_format($order->artisanMerchandiseShare(), 2) }}</span></div>
                    <p class="small text-muted mb-3">Rough guide after any promo savings and marketplace fee.</p>
                    <div class="d-flex justify-content-between mb-2 mt-3"><span>Est. delivery window</span><span>{{ $order->estimated_delivery_date }}</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-0"><span>Status</span><x-status-badge :status="$order->status" type="order" /></div>
                    <div class="d-flex justify-content-between align-items-center mt-2"><span>Delivery</span><x-status-badge :status="$order->delivery_status" type="delivery" /></div>
                    @if($order->rider)
                        <p class="small text-muted mt-2 mb-0">Assigned rider: {{ $order->rider->full_name }} ({{ $order->rider->contact_number }})</p>
                    @endif
                </div>
            </div>

            @php($payCod = $order->payment && strtolower((string) $order->payment->payment_method) === 'cod')

            @if($payCod || $ledgerSnapshot)
                <div class="card mb-3 border-primary border-opacity-25">
                    <div class="card-header py-3">
                        <h5 class="mb-0 fw-semibold">Pay on delivery — amounts</h5>
                    </div>
                    <div class="card-body small">
                        <p class="text-muted mb-2">Helps you align with riders and the office. Actual cash handoffs follow your usual process; <strong>payment records</strong> are final after delivery is posted.</p>
                        <p class="mb-2"><span class="text-muted">Allocation display:</span>
                            @if($order->packages->isNotEmpty())
                                {{ $packageAllocations[$order->packages->first()->id]['policy_label'] ?? '—' }}
                            @else
                                —
                            @endif
                        </p>
                        @if($ledgerSnapshot)
                            <div class="alert alert-light border mb-2 py-2 mb-3">
                                <div class="fw-semibold mb-1">Recorded payment (ref #{{ $ledgerSnapshot['journal_id'] }})</div>
                                <div class="row g-2">
                                    <div class="col-6">Your share (items)</div><div class="col-6 text-end fw-semibold text-success">₱{{ number_format($ledgerSnapshot['artisan_payable'], 2) }}</div>
                                    <div class="col-6">From buyer</div><div class="col-6 text-end">₱{{ number_format($ledgerSnapshot['cod_collectible'], 2) }}</div>
                                    <div class="col-6">Likha service fee</div><div class="col-6 text-end">₱{{ number_format($ledgerSnapshot['platform_service_fee'], 2) }}</div>
                                    <div class="col-6">Shipping (buyer portion)</div><div class="col-6 text-end">₱{{ number_format($ledgerSnapshot['shipping_trust'], 2) }}</div>
                                    <div class="col-6">Tax (on receipt)</div><div class="col-6 text-end">₱{{ number_format($ledgerSnapshot['tax_payable'], 2) }}</div>
                                </div>
                                <a href="{{ route('artisan.ledger.show', $ledgerSnapshot['journal_id']) }}" class="btn btn-sm btn-outline-primary mt-2">Open full breakdown</a>
                            </div>
                        @else
                            <p class="mb-2"><span class="text-muted">Payment record:</span> not saved yet — appears after every package is delivered.</p>
                        @endif

                        @if($payCod && $order->isDelivered())
                            @if($order->sellerCodHandoff?->acknowledged_at)
                                <div class="alert alert-success py-2 mb-0">
                                    You confirmed rider handoff on {{ $order->sellerCodHandoff->acknowledged_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}.
                                    @if($order->sellerCodHandoff->note)<div class="small mt-1">{{ $order->sellerCodHandoff->note }}</div>@endif
                                </div>
                            @else
                                <form method="POST" action="{{ route('artisan.orders.cod-handoff.store', $order) }}" class="border rounded p-3 bg-light">
                                    @csrf
                                    <div class="fw-semibold mb-2">Confirm rider paid your goods portion</div>
                                    <p class="mb-2">Expected amount for your items (from records if saved): <strong>₱{{ number_format($ledgerSnapshot['artisan_payable'] ?? $order->artisanMerchandiseShare(), 2) }}</strong></p>
                                    <label class="form-label small mb-1">Optional note</label>
                                    <input type="text" name="note" class="form-control form-control-sm mb-2" maxlength="500" placeholder="e.g. Received cash today">
                                    <button type="submit" class="btn btn-success btn-sm">Confirm handoff received</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            @if($order->packages->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Delivery packages</h5>
                </div>
                <div class="card-body">
                    @foreach($order->packages as $pkg)
                        @php($alloc = $packageAllocations[$pkg->id] ?? null)
                        <div class="border rounded p-2 mb-3 mb-md-2">
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="small fw-semibold">Package {{ $pkg->sequence }}</span>
                                <x-status-badge :status="$pkg->delivery_status" type="delivery" />
                            </div>
                            @if($pkg->rider)
                                <p class="small text-muted mb-1">Rider: {{ $pkg->rider->full_name }}</p>
                            @endif
                            @if($alloc && $payCod && $pkg->isDelivered())
                                <div class="small mt-2 pt-2 border-top">
                                    <div class="text-muted mb-1">Attributed planning amounts for this stop</div>
                                    <div class="d-flex justify-content-between"><span>Goods due to you</span><span class="text-success fw-semibold">₱{{ number_format($alloc['seller_share'], 2) }}</span></div>
                                    <div class="d-flex justify-content-between"><span>COD slice (attrib.)</span><span>₱{{ number_format($alloc['cod_total'], 2) }}</span></div>
                                    @if(!empty($alloc['physical_cod_hint']))
                                        <p class="text-muted mb-0 mt-1">{{ $alloc['physical_cod_hint'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

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
                <button type="submit" class="btn btn-primary w-100">Approve & mark as shipped</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
