@extends('layouts.app')

@section('title', 'Delivery Monitoring')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h3 mb-3">Delivery Monitoring</h1>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="delivery_status" class="form-select">
                <option value="">All statuses</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(request('delivery_status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="rider_id" class="form-select">
                <option value="">All riders</option>
                @foreach($riders as $rider)
                    <option value="{{ $rider->id }}" @selected((string) request('rider_id') === (string) $rider->id)>{{ $rider->full_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><input type="date" name="delivery_date" class="form-control" value="{{ request('delivery_date') }}"></div>
        <div class="col-md-3"><button class="btn btn-outline-primary w-100">Apply filters</button></div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Rider</th>
                        <th>Delivery status</th>
                        <th>Assigned at</th>
                        <th>Completed at</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td>{{ $order->rider?->full_name ?? 'Unassigned' }}</td>
                            <td><x-status-badge :status="$order->delivery_status" type="delivery" /></td>
                            <td>{{ $order->delivery_assigned_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td>{{ $order->delivery_completed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-end">
                                @if(!$order->rider_id)
                                    <form action="{{ route('admin.deliveries.assign', $order) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Assign rider</button></form>
                                @endif
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#status-{{ $order->id }}">Update</button>
                            </td>
                        </tr>
                        <tr class="collapse" id="status-{{ $order->id }}">
                            <td colspan="7">
                                <form action="{{ route('admin.deliveries.status', $order) }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="col-md-4">
                                        <select class="form-select" name="delivery_status" required>
                                            @foreach($statusOptions as $value => $label)
                                                @if($value !== 'pending_assignment')
                                                <option value="{{ $value }}" @selected($order->delivery_status === $value)>{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6"><input name="note" class="form-control" placeholder="Optional note"></div>
                                    <div class="col-md-2"><button class="btn btn-primary w-100">Save</button></div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No deliveries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $deliveries->links() }}</div>
</div>
@endsection
