@extends('layouts.app')

@section('title', 'Returns')

@section('content')
<div class="container py-2 py-md-3">
    <x-profile-header-nav active="returns" />
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h2 fw-semibold mb-0">Returns on your orders</h1>
        <a href="{{ route('artisan.orders.index') }}" class="btn btn-outline-secondary btn-sm">Orders</a>
    </div>

    <form method="GET" class="mb-3">
        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="{{ \App\Models\OrderItemReturn::STATUS_PENDING_ADMIN }}" @selected(request('status') === \App\Models\OrderItemReturn::STATUS_PENDING_ADMIN)>Pending review</option>
            <option value="{{ \App\Models\OrderItemReturn::STATUS_APPROVED }}" @selected(request('status') === \App\Models\OrderItemReturn::STATUS_APPROVED)>Approved</option>
            <option value="{{ \App\Models\OrderItemReturn::STATUS_REJECTED }}" @selected(request('status') === \App\Models\OrderItemReturn::STATUS_REJECTED)>Rejected</option>
        </select>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>When</th>
                        <th>Order</th>
                        <th>Item</th>
                        <th>Buyer</th>
                        <th>Qty</th>
                        <th>Reason</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $ret)
                        <tr>
                            <td class="small text-muted">{{ $ret->created_at?->format('M d, Y H:i') }}</td>
                            <td><a href="{{ route('artisan.orders.show', $ret->order) }}">{{ $ret->order?->order_number }}</a></td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($ret->orderItem?->product_name ?? '—', 40) }}</td>
                            <td class="small">{{ $ret->customer?->name ?? '—' }}</td>
                            <td>{{ $ret->quantity }}</td>
                            <td class="small">{{ $ret->reasonLabel() }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($ret->notes ?? '', 40) }}</td>
                            <td><span class="badge bg-secondary">{{ $ret->statusLabel() }}</span></td>
                            <td class="text-end"><a href="{{ route('artisan.returns.show', $ret) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No returns yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $returns->links() }}</div>
</div>
@endsection
