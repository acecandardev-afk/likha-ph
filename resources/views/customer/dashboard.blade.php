@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h2 fw-semibold mb-4">My Dashboard</h1>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-md-4 mb-4">
        <div class="col">
            <div class="card stat-card h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Total orders</p>
                    <h2 class="h3 mb-0">{{ $stats['total_orders'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 border-warning">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Pending orders</p>
                    <h2 class="h3 mb-0">{{ $stats['pending_orders'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 border-success">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Completed orders</p>
                    <h2 class="h3 mb-0">{{ $stats['completed_orders'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 bg-primary bg-opacity-10 border-primary">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Total spent</p>
                    <h2 class="h3 mb-0 text-primary">₱{{ number_format($stats['total_spent'], 0) }}</h2>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card stat-card h-100 bg-info bg-opacity-10 border-info">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Confirmed orders</p>
                    <h2 class="h3 mb-0 text-info">{{ $stats['confirmed_orders'] ?? 0 }}</h2>
                    <small class="text-muted">Ready for fulfillment</small>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card stat-card h-100 bg-warning bg-opacity-10 border-warning">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Avg order value</p>
                    @php
                        $avgValue = ($stats['total_orders'] ?? 0) > 0
                            ? ($stats['total_spent'] / $stats['total_orders'])
                            : 0;
                    @endphp
                    <h2 class="h3 mb-0">₱{{ number_format($avgValue, 0) }}</h2>
                    <small class="text-muted">Based on your history</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Quick links</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-bag me-1"></i> Continue shopping
                        </a>
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-receipt me-1"></i> Orders
                        </a>
                        <a href="{{ route('account.edit') }}" class="btn btn-outline-dark w-100">
                            <i class="bi bi-geo-alt me-1"></i> Shipping
                        </a>
                        @if(!auth()->user()->isArtisan())
                            <a href="{{ route('register.artisan') }}" class="btn btn-outline-dark w-100">
                                Apply to sell as an artisan
                            </a>
                        @endif
                    </div>
                    <p class="text-muted small mb-0 mt-3">Track updates faster from your order list.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-semibold">Most ordered artisans (recent)</h5>
                    <small class="text-muted">From your latest orders</small>
                </div>
                <div class="card-body">
                    @php
                        $topArtisans = $recentOrders
                            ->groupBy(fn($o) => $o->artisan?->name ?? 'Unknown')
                            ->sortByDesc(fn($g) => $g->count())
                            ->take(5);
                    @endphp
                    @forelse($topArtisans as $name => $group)
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div class="fw-semibold">{{ $name }}</div>
                            <div class="text-muted small">{{ $group->count() }} order(s)</div>
                        </div>
                    @empty
                        <div class="text-muted small">No orders yet. Start shopping to see artisan highlights.</div>
                    @endforelse
                    <div class="mt-3">
                        <a href="{{ route('artisans.index') }}" class="btn btn-sm btn-outline-primary">Browse artisans</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <h5 class="mb-0 fw-semibold">Recent orders</h5>
            <a href="{{ route('customer.orders.index') }}" class="btn btn-sm btn-outline-primary">View all orders</a>
        </div>
        <div class="card-body">
            @if($recentOrders->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted">You haven’t placed any orders yet.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Start shopping</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Artisan</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ Str::limit($order->artisan?->name ?? 'Unknown Artisan', 15) }}</td>
                                    <td>{{ $order->items->count() }} item(s)</td>
                                    <td>₱{{ number_format($order->total, 0) }}</td>
                                    <td><x-status-badge :status="$order->status" type="order" /></td>
                                    <td>
                                        @if($order->payment)
                                            <x-status-badge :status="$order->payment->verification_status" type="payment" />
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at?->format('M d, Y') ?? '' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
