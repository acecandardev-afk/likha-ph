@extends('layouts.app')

@section('title', 'My deliveries')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">My Deliveries</h1>
        <a href="{{ route('rider.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $order->formattedShippingAddress() }}</td>
                            <td><x-status-badge :status="$order->delivery_status" type="delivery" /></td>
                            <td>{{ $order->delivery_assigned_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-end"><a href="{{ route('rider.deliveries.show', $order) }}" class="btn btn-sm btn-primary">Update progress</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No assigned deliveries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
