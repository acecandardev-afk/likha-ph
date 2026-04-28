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
                        <p class="text-muted small fw-medium mb-1">Order value (confirmed)</p>
                        <h2 class="h3 mb-0">₱{{ number_format($stats['total_revenue'], 0) }}</h2>
                        <p class="small text-muted mb-0 mt-2">Platform fee realized: <strong>₱{{ number_format($stats['realized_platform_revenue'] ?? 0, 2) }}</strong></p>
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

    <div class="row g-4 mt-2">
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
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Available riders</div>
                            <div class="fw-semibold fs-4">{{ $stats['available_riders'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Completed deliveries</div>
                            <div class="fw-semibold fs-4">{{ $stats['completed_deliveries'] }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Open delivery reports</div>
                            <div class="fw-semibold fs-4">{{ $stats['open_delivery_reports'] ?? 0 }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small fw-medium">Platform fee realized</div>
                            <div class="fw-semibold fs-4">₱{{ number_format($stats['realized_platform_revenue'] ?? 0, 0) }}</div>
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
