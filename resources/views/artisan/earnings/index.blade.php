@extends('layouts.app')

@section('title', 'Your earnings')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">After delivery</h1>
            <p class="text-muted small mb-2">A simple view of settled orders and how much stays with you from sales after fees. Courier amounts are what the platform pays riders from its side — not taken from your prices.</p>
            <p class="small mb-0"><a href="{{ route('artisan.ledger.index') }}">Payment records</a> are the official line-by-line breakdown; each order page shows how cash is split for riders vs what was posted.</p>
        </div>
        <a href="{{ route('artisan.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card stat-card h-100 border-success bg-success bg-opacity-10">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Your estimated share so far</p>
                    <h2 class="h3 mb-0">₱{{ number_format($totals['estimated_share'], 2) }}</h2>
                    <small class="text-muted">Across {{ $totals['orders_count'] }} completed or delivered {{ $totals['orders_count'] === 1 ? 'order' : 'orders' }}</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card stat-card h-100">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Courier fees (for your info)</p>
                    <h2 class="h3 mb-0">₱{{ number_format($totals['courier_fees_on_record'], 2) }}</h2>
                    <small class="text-muted">Recorded on packages when delivered — paid by the platform to riders.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-semibold">Recent settled orders</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Buyer</th>
                            <th class="text-end" scope="col">Buyer pays</th>
                            <th class="text-end" scope="col">Your share (est.)</th>
                            <th scope="col">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('artisan.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->order_number }}</a>
                                </td>
                                <td class="small">{{ $order->customer?->name ?? '—' }}</td>
                                <td class="text-end">₱{{ number_format($order->total, 2) }}</td>
                                <td class="text-end text-success fw-semibold">₱{{ number_format($order->artisanMerchandiseShare(), 2) }}</td>
                                <td class="small text-muted">{{ $order->created_at?->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No delivered or completed orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
