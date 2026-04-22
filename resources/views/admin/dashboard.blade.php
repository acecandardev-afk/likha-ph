@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h2 fw-semibold mb-4">Admin Dashboard</h1>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-md-4 mb-4">
        <div class="col">
            <div class="card stat-card h-100 bg-warning bg-opacity-10 border-warning">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-medium mb-1">Pending products</p>
                        <h2 class="h3 mb-2">{{ $stats['pending_products'] }}</h2>
                        <a href="{{ route('admin.products.pending') }}" class="btn btn-sm btn-warning">Review</a>
                    </div>
                    <i class="bi bi-box-seam text-warning opacity-75 fs-2"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 bg-info bg-opacity-10 border-info">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-medium mb-1">Pending payments</p>
                        <h2 class="h3 mb-2">{{ $stats['pending_payments'] }}</h2>
                        <a href="{{ route('admin.payments.pending') }}" class="btn btn-sm btn-info">Verify</a>
                    </div>
                    <i class="bi bi-credit-card text-info opacity-75 fs-2"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 bg-success bg-opacity-10 border-success">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-medium mb-1">Total revenue</p>
                        <h2 class="h3 mb-0">₱{{ number_format($stats['total_revenue'], 0) }}</h2>
                    </div>
                    <i class="bi bi-cash-stack text-success opacity-75 fs-2"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card h-100 bg-primary bg-opacity-10 border-primary">
                <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-medium mb-1">Total orders</p>
                        <h2 class="h3 mb-0">{{ $stats['total_orders'] }}</h2>
                    </div>
                    <i class="bi bi-receipt text-primary opacity-75 fs-2"></i>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card stat-card h-100 border-warning bg-warning bg-opacity-10">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Verified artisans</p>
                    <h2 class="h3 mb-1">{{ $stats['total_artisans'] }}</h2>
                    <small class="text-muted">{{ $stats['pending_products'] }} pending items need review</small>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card stat-card h-100 bg-danger bg-opacity-10 border-danger">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Unapproved reviews</p>
                    <h2 class="h3 mb-1">{{ $stats['unapproved_reviews'] }}</h2>
                    <small class="text-danger">Keep quality high by approving quickly</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Needs attention</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="text-muted small fw-medium">Pending products</div>
                            <div class="fw-semibold fs-4">{{ $stats['pending_products'] }}</div>
                        </div>
                        <a href="{{ route('admin.products.pending') }}" class="btn btn-sm btn-outline-warning">Review</a>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="text-muted small fw-medium">Pending payments</div>
                            <div class="fw-semibold fs-4">{{ $stats['pending_payments'] }}</div>
                        </div>
                        <a href="{{ route('admin.payments.pending') }}" class="btn btn-sm btn-outline-info">Verify</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Quick actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <a href="{{ route('admin.products.approved') }}" class="btn btn-sm btn-outline-success w-100">
                                <i class="bi bi-check2-circle me-1"></i> Approved
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('admin.products.rejected') }}" class="btn btn-sm btn-outline-danger w-100">
                                <i class="bi bi-x-circle me-1"></i> Rejected
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('admin.payments.verified') }}" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-patch-check me-1"></i> Verified
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                                <i class="bi bi-receipt-cutoff me-1"></i> Sales
                            </a>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-3">Tip: keep review queues low for faster customer trust.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Top pending categories</h5>
                </div>
                <div class="card-body">
                    @php
                        $topCategories = $recentProducts
                            ->groupBy(fn($p) => $p->category?->name ?? 'Uncategorized')
                            ->sortByDesc(fn($group) => $group->count())
                            ->take(5);
                    @endphp
                    @forelse($topCategories as $catName => $items)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="text-muted small">{{ $catName }}</div>
                            <div class="fw-semibold">{{ $items->count() }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">No pending products yet.</div>
                    @endforelse
                    <div class="mt-3">
                        <a href="{{ route('admin.products.pending') }}" class="btn btn-sm btn-outline-primary w-100">View full queue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.products.pending') }}" class="btn btn-outline-secondary btn-sm">Products</a>
        <a href="{{ route('admin.products.approved') }}" class="btn btn-outline-success btn-sm">Approved products</a>
        <a href="{{ route('admin.payments.pending') }}" class="btn btn-outline-info btn-sm">Payments</a>
        <a href="{{ route('admin.payments.verified') }}" class="btn btn-outline-success btn-sm">Verified payments</a>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-primary btn-sm">Sales</a>
        <a href="{{ route('admin.users.artisans') }}" class="btn btn-outline-secondary btn-sm">Artisans</a>
        <a href="{{ route('admin.users.customers') }}" class="btn btn-outline-secondary btn-sm">Customers</a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">Categories</a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Recent pending products</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Artisan</th>
                                    <th>Price</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProducts as $product)
                                    <tr>
                                        <td>{{ Str::limit($product->name, 25) }}</td>
                                        <td>{{ Str::limit($product->artisan?->name ?? 'Unknown Artisan', 15) }}</td>
                                        <td>₱{{ number_format($product->price, 0) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.products.review', $product) }}" class="btn btn-sm btn-primary">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No pending products</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Recent pending payments</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->order?->order_number ?? '-' }}</td>
                                        <td>{{ Str::limit($payment->order?->customer?->name ?? 'Unknown Customer', 15) }}</td>
                                        <td>₱{{ number_format($payment->amount, 0) }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.payments.review', $payment) }}" class="btn btn-sm btn-primary">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No pending payments</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0 fw-semibold">Recent orders</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Artisan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ Str::limit($order->customer?->name ?? 'Unknown Customer', 18) }}</td>
                                <td>{{ Str::limit($order->artisan?->name ?? 'Unknown Artisan', 18) }}</td>
                                <td>₱{{ number_format($order->total, 0) }}</td>
                                <td><x-status-badge :status="$order->status" type="order" /></td>
                                <td>{{ $order->created_at?->format('M d, Y') ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No orders yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">System snapshot</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Pending orders</div>
                            <div class="fw-semibold fs-4">{{ $stats['pending_orders'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Total customers</div>
                            <div class="fw-semibold fs-4">{{ $stats['total_customers'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Pending products</div>
                            <div class="fw-semibold fs-4">{{ $stats['pending_products'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Unapproved reviews</div>
                            <div class="fw-semibold fs-4">{{ $stats['unapproved_reviews'] }}</div>
                        </div>
                    </div>
                    <p class="text-muted small mb-0 mt-3">Use the tables above to take action quickly.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Activity timeline</h5>
                </div>
                <div class="card-body">
                    @php
                        $timeline = collect();
                        $timeline = $timeline->merge(
                            $recentProducts->map(fn($p) => [
                                'type' => 'product',
                                'label' => 'Pending product: ' . ($p->name ?? ''),
                                'meta' => '₱' . number_format($p->price ?? 0, 0),
                                'route' => route('admin.products.review', $p),
                            ])
                        );
                        $timeline = $timeline->merge(
                            $recentPayments->map(fn($pay) => [
                                'type' => 'payment',
                                'label' => 'Pending payment: ' . ($pay->order?->order_number ?? ''),
                                'meta' => '₱' . number_format($pay->amount ?? 0, 0),
                                'route' => route('admin.payments.review', $pay),
                            ])
                        );
                        $timeline = $timeline->take(8);
                    @endphp

                    @forelse($timeline as $item)
                        <div class="d-flex align-items-start justify-content-between gap-3 py-2 border-bottom">
                            <div>
                                <div class="fw-semibold small">{{ $item['type'] === 'product' ? 'Product' : 'Payment' }}</div>
                                <div class="small">{{ $item['label'] }}</div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">{{ $item['meta'] }}</div>
                                <a href="{{ $item['route'] }}" class="small fw-semibold btn-link text-decoration-none">Open</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">No recent activity.</div>
                    @endforelse
                    <div class="mt-3">
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-primary w-100">Go to sales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
