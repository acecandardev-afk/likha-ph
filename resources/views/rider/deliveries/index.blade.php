@extends('layouts.app')

@section('title', 'Deliveries')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h4 fw-semibold mb-3">My packages</h1>

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
                        <th>Order · Package</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $pkg)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $pkg->order->order_number }}</span>
                                <span class="text-muted small">· Pkg {{ $pkg->sequence }}</span>
                            </td>
                            <td>{{ $pkg->order->customer?->name ?? '—' }}</td>
                            <td><x-status-badge :status="$pkg->delivery_status" type="delivery" /></td>
                            <td class="text-end">
                                <a href="{{ route('rider.deliveries.show', $pkg) }}" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No packages assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $packages->links() }}</div>
</div>
@endsection
