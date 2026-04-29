@extends('layouts.app')

@section('title', 'Delivery Monitoring')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h3 mb-3">Delivery Monitoring</h1>
    <p class="text-muted small mb-4">Each row is one physical package. Riders can carry multiple concurrent deliveries as operations allow. Delivered packages are locked for editing.</p>

    @if ($errors->has('delivery'))
        <div class="alert alert-danger">{{ $errors->first('delivery') }}</div>
    @endif

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
                        <th>Order · Package</th>
                        <th>Customer</th>
                        <th>Rider</th>
                        <th>Delivery status</th>
                        <th>Assigned at</th>
                        <th>Completed at</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $pkg)
                        <tr @class(['table-secondary' => $pkg->isDelivered(), 'opacity-75' => $pkg->isDelivered()])>
                            <td>
                                <span class="fw-semibold">{{ $pkg->order->order_number }}</span>
                                <span class="text-muted small">· Pkg {{ $pkg->sequence }}</span>
                            </td>
                            <td>{{ $pkg->order->customer?->name ?? '—' }}</td>
                            <td>
                                @if($pkg->rider)
                                    <a href="{{ route('admin.riders.show', $pkg->rider) }}" class="text-decoration-none fw-medium">{{ $pkg->rider->full_name }}</a>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td><x-status-badge :status="$pkg->delivery_status" type="delivery" /></td>
                            <td>{{ $pkg->delivery_assigned_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td>{{ $pkg->delivery_completed_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-end">
                                @if(!$pkg->rider_id && !$pkg->isDelivered())
                                    <form action="{{ route('admin.deliveries.assign', $pkg) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Assign rider</button></form>
                                @endif
                                @if($pkg->isDelivered())
                                    <span class="badge text-bg-secondary">Locked</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#status-{{ $pkg->id }}">Update</button>
                                @endif
                            </td>
                        </tr>
                        @if(!$pkg->isDelivered())
                        <tr class="collapse" id="status-{{ $pkg->id }}">
                            <td colspan="7" class="bg-light">
                                <form action="{{ route('admin.deliveries.status', $pkg) }}" method="POST" class="row g-2 p-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="col-md-4">
                                        <select class="form-select" name="delivery_status" required>
                                            @foreach($statusOptions as $value => $label)
                                                @if($value !== 'pending_assignment')
                                                <option value="{{ $value }}" @selected($pkg->delivery_status === $value)>{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6"><input name="note" class="form-control" placeholder="Optional note"></div>
                                    <div class="col-md-2"><button class="btn btn-primary w-100">Save</button></div>
                                </form>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No packages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $packages->links() }}</div>
</div>
@endsection
