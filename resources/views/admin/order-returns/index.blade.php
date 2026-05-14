@extends('layouts.app')

@section('title', 'Item returns')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Item returns</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Item returns</h1>

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
                        <th>Seller</th>
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
                            <td>{{ $ret->order?->order_number }}</td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($ret->orderItem?->product_name ?? '—', 36) }}</td>
                            <td class="small">{{ $ret->customer?->name ?? '—' }}</td>
                            <td class="small">{{ $ret->artisan?->name ?? '—' }}</td>
                            <td class="small">{{ $ret->reasonLabel() }}</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($ret->notes ?? '', 48) }}</td>
                            <td><span class="badge bg-secondary">{{ $ret->statusLabel() }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.order-returns.show', $ret) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No return requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $returns->links() }}</div>
</div>
@endsection
