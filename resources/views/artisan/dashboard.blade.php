@extends('layouts.app')

@section('title', 'Artisan Dashboard')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <h1 class="h2 fw-semibold mb-0">Dashboard</h1>
        <a href="{{ route('artisan.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add product
        </a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-md-4 mb-4">
        <div class="col">
            <div class="card stat-card h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Total products</p>
                    <h2 class="h3 mb-1">{{ $stats['total_products'] }}</h2>
                    <small class="text-success">{{ $stats['approved_products'] }} approved</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Pending approval</p>
                    <h2 class="h3 mb-1">{{ $stats['pending_products'] }}</h2>
                    @if($stats['rejected_products'] > 0)
                        <small class="text-danger">{{ $stats['rejected_products'] }} rejected</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Total orders</p>
                    <h2 class="h3 mb-1">{{ $stats['total_orders'] }}</h2>
                    <small class="text-warning">{{ $stats['pending_orders'] }} pending</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 bg-success bg-opacity-10 border-success">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Total revenue</p>
                    <h2 class="h3 mb-1 text-success">₱{{ number_format($stats['total_revenue'], 0) }}</h2>
                    <small class="text-muted">₱{{ number_format($stats['monthly_revenue'], 0) }} this month</small>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card stat-card h-100 bg-primary bg-opacity-10 border-primary">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Confirmed orders</p>
                    <h2 class="h3 mb-1 text-primary">{{ $stats['confirmed_orders'] }}</h2>
                    <small class="text-muted">In progress with verified payments</small>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card stat-card h-100 bg-danger bg-opacity-10 border-danger">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Rejected products</p>
                    <h2 class="h3 mb-1 text-danger">{{ $stats['rejected_products'] }}</h2>
                    <small class="text-muted">Fix listing details to get approved</small>
                </div>
            </div>
        </div>
    </div>

    @if($lowStockProducts->isNotEmpty())
        <div class="alert alert-warning d-flex align-items-start mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2 mt-1"></i>
            <div>
                <strong>Low stock</strong> — The following products are running low:
                <ul class="mb-0 mt-2">
                    @foreach($lowStockProducts as $product)
                        <li>
                            <strong>{{ $product->name }}</strong> — {{ $product->stock }} left
                            <a href="{{ route('artisan.products.edit', $product) }}" class="ms-1">Update stock</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Quick actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('artisan.products.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-box-seam me-1"></i> My products
                        </a>
                        <a href="{{ route('artisan.orders.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-receipt me-1"></i> Orders
                        </a>
                        <a href="{{ route('artisan.profile.edit') }}" class="btn btn-outline-dark w-100">
                            <i class="bi bi-person-circle me-1"></i> Profile
                        </a>
                    </div>
                    <p class="text-muted small mb-0 mt-3">Pro tip: keep your listing images sharp for better approvals.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-semibold">Order progress</h5>
                    <small class="text-muted">Latest status overview</small>
                </div>
                <div class="card-body">
                    @php
                        $total = max(1, (int)($stats['total_orders'] ?? 0));
                        $pendingPct = round((($stats['pending_orders'] ?? 0) / $total) * 100);
                        $confirmedPct = round((($stats['confirmed_orders'] ?? 0) / $total) * 100);
                        $completedPct = round((($stats['completed_orders'] ?? 0) / $total) * 100);
                    @endphp
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="text-muted small fw-medium mb-1">Pending</div>
                            <div class="fw-semibold fs-4">{{ $stats['pending_orders'] }}</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPct }}%" aria-valuenow="{{ $pendingPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-muted small fw-medium mb-1">Confirmed</div>
                            <div class="fw-semibold fs-4 text-primary">{{ $stats['confirmed_orders'] }}</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $confirmedPct }}%" aria-valuenow="{{ $confirmedPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-muted small fw-medium mb-1">Completed</div>
                            <div class="fw-semibold fs-4 text-success">{{ $stats['completed_orders'] }}</div>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $completedPct }}%" aria-valuenow="{{ $completedPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('artisan.orders.index') }}" class="btn btn-sm btn-outline-primary">Manage orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-semibold">Recent products</h5>
                    <a href="{{ route('artisan.products.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProducts as $product)
                                    <tr>
                                        <td>{{ Str::limit($product->name, 28) }}</td>
                                        <td>₱{{ number_format($product->price, 0) }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td><x-status-badge :status="$product->approval_status" type="product" /></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No products yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-semibold">Recent orders</h5>
                    <a href="{{ route('artisan.orders.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('artisan.orders.show', $order) }}" class="text-decoration-none">{{ $order->order_number }}</a>
                                        </td>
                                        <td>{{ Str::limit($order->customer?->name ?? 'Unknown Customer', 18) }}</td>
                                        <td>₱{{ number_format($order->total, 0) }}</td>
                                        <td><x-status-badge :status="$order->status" type="order" /></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No orders yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Earnings snapshot</h5>
                </div>
                <div class="card-body">
                    @php
                        $orderCount = max(1, (int) $recentOrders->count());
                        $avgOrder = $recentOrders->sum('total') / $orderCount;
                    @endphp
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small fw-medium mb-1">Monthly revenue</div>
                            <div class="fw-semibold fs-4 text-success">₱{{ number_format($stats['monthly_revenue'], 0) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium mb-1">Avg recent order</div>
                            <div class="fw-semibold fs-4">₱{{ number_format($avgOrder, 0) }}</div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-3">This dashboard uses your latest orders to show quick signals.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">What to improve next</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">
                            <span class="fw-semibold">Clear pending approvals:</span>
                            {{ $stats['pending_products'] }} products are waiting.
                        </li>
                        <li class="mb-2">
                            <span class="fw-semibold">Resolve rejections:</span>
                            {{ $stats['rejected_products'] }} items were rejected.
                        </li>
                        <li class="mb-0">
                            <span class="fw-semibold">Avoid stockouts:</span>
                            @if($lowStockProducts->isNotEmpty())
                                Update {{ $lowStockProducts->count() }} low-stock items.
                            @else
                                You are good to go right now.
                            @endif
                        </li>
                    </ul>
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a href="{{ route('artisan.products.index') }}" class="btn btn-sm btn-outline-primary">View products</a>
                        <a href="{{ route('artisan.products.create') }}" class="btn btn-sm btn-primary">Add new</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
