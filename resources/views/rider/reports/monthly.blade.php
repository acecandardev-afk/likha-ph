@extends('layouts.app')

@section('title', $title ?? 'Monthly report')

@section('content')
<div class="container py-2 py-md-3 report-print-root">
    <nav aria-label="Breadcrumb" class="mb-3 no-print">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('rider.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Monthly report</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h3 fw-semibold mb-1">Monthly report — {{ $report['window']['label'] }}</h1>
            <p class="text-muted small mb-0">{{ $report['rider']->full_name }} · {{ $report['rider']->rider_id }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print">
            <form method="get" class="d-flex flex-wrap gap-2 align-items-end filter-month-form">
                <div>
                    <label class="form-label small mb-0">Year</label>
                    <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2020" max="2100">
                </div>
                <div>
                    <label class="form-label small mb-0">Month</label>
                    <input type="number" name="month" class="form-control form-control-sm" value="{{ $month }}" min="1" max="12">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </form>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Packages delivered</div>
                    <div class="h4 mb-0">{{ number_format($report['packages_delivered']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Rider fees recorded</div>
                    <div class="h4 mb-0">₱{{ number_format($report['rider_fees_total'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h6 text-uppercase text-muted mb-2">Deliveries completed in period</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Completed</th>
                    <th class="text-end">Rider fee</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['packages'] as $pkg)
                    <tr>
                        <td>
                            <a href="{{ route('rider.deliveries.show', $pkg) }}" class="no-print">{{ $pkg->order?->order_number ?? '#' }}</a>
                            <span class="d-none d-print-inline">{{ $pkg->order?->order_number ?? '—' }}</span>
                        </td>
                        <td>{{ $pkg->order?->customer?->name ?? '—' }}</td>
                        <td>{{ $pkg->delivery_completed_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="text-end">₱{{ number_format((float) ($pkg->rider_fee_amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No completed deliveries in this month.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="small text-muted mt-4 mb-0">Generated {{ $report['generated_at']->format('M d, Y H:i') }}</p>
</div>
@endsection
