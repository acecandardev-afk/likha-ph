@extends('layouts.app')

@section('title', 'My returns')

@section('content')
<div class="container py-2 py-md-3">
    <x-profile-header-nav active="returns" />
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h2 fw-semibold mb-0">My returns</h1>
        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary btn-sm">Back to orders</a>
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
                            <td><a href="{{ route('customer.orders.show', $ret->order) }}">{{ $ret->order?->order_number }}</a></td>
                            <td>{{ \Illuminate\Support\Str::limit($ret->orderItem?->product_name ?? '—', 40) }}</td>
                            <td>{{ $ret->quantity }}</td>
                            <td class="small">{{ $ret->reasonLabel() }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($ret->notes ?? '', 40) }}</td>
                            <td><span class="badge bg-secondary">{{ $ret->statusLabel() }}</span></td>
                            <td class="text-end"><a href="{{ route('customer.returns.show', $ret) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No return requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $returns->links() }}</div>
</div>
@endsection
