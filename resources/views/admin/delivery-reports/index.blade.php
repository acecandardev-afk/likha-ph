@extends('layouts.app')

@section('title', 'Delivery reports')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Delivery reports</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Delivery reports</h1>

    <form method="GET" class="mb-3">
        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="{{ \App\Models\DeliveryReport::STATUS_OPEN }}" @selected(request('status') === \App\Models\DeliveryReport::STATUS_OPEN)>Open</option>
            <option value="{{ \App\Models\DeliveryReport::STATUS_REVIEWED }}" @selected(request('status') === \App\Models\DeliveryReport::STATUS_REVIEWED)>Reviewed</option>
            <option value="{{ \App\Models\DeliveryReport::STATUS_RESOLVED }}" @selected(request('status') === \App\Models\DeliveryReport::STATUS_RESOLVED)>Resolved</option>
        </select>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>When</th>
                        <th>Order · Pkg</th>
                        <th>Reporter</th>
                        <th>Concern</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td class="small text-muted">{{ $report->created_at?->format('M d, Y H:i') }}</td>
                            <td>
                                {{ $report->orderPackage->order->order_number ?? '—' }}
                                <span class="text-muted">· Pkg {{ $report->orderPackage->sequence ?? '—' }}</span>
                            </td>
                            <td>{{ $report->reporter?->name ?? '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($report->concern, 48) }}</td>
                            <td><span class="badge bg-secondary">{{ $report->status }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.delivery-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No reports.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $reports->links() }}</div>
</div>
@endsection
