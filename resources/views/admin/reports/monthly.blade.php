@extends('layouts.app')

@section('title', $title ?? 'Monthly report')

@section('content')
<div class="container py-2 py-md-3 report-print-root">
    <nav aria-label="Breadcrumb" class="mb-3 no-print">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Monthly report</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h3 fw-semibold mb-1">Monthly report — {{ $report['window']['label'] }}</h1>
            <p class="text-muted small mb-0">Platform overview. Printed {{ $report['generated_at']->format('M d, Y H:i') }}.</p>
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
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Orders placed (month)</div>
                    <div class="h4 mb-0">{{ number_format($report['orders_count']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">GMV (delivered / completed)</div>
                    <div class="h4 mb-0">₱{{ number_format($report['gmv_delivered_completed'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Verified payment intake (created)</div>
                    <div class="h4 mb-0">₱{{ number_format($report['verified_payments_sum'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">Delivered packages</div>
                    <div class="fs-5 fw-semibold">{{ number_format($report['delivered_packages']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">New customers</div>
                    <div class="fs-5 fw-semibold">{{ number_format($report['new_customers']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">New artisans</div>
                    <div class="fs-5 fw-semibold">{{ number_format($report['new_artisans']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-light h-100">
                <div class="card-body py-3">
                    <div class="small text-muted">Products listed (new)</div>
                    <div class="fs-5 fw-semibold">{{ number_format($report['products_created']) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($report['by_status']))
        <h2 class="h6 text-uppercase text-muted mb-2">Orders by status</h2>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered">
                <thead class="table-light"><tr><th>Status</th><th class="text-end">Count</th></tr></thead>
                <tbody>
                    @foreach($report['by_status'] as $st => $cnt)
                        <tr>
                            <td>{{ $st }}</td>
                            <td class="text-end">{{ number_format($cnt) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h2 class="h6 text-uppercase text-muted mb-2">Order lines (up to 100)</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle small">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Artisan</th>
                    <th class="text-end">Total</th>
                    <th>Status</th>
                    <th>Placed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['orders'] as $o)
                    <tr>
                        <td>{{ $o->order_number }}</td>
                        <td>{{ $o->customer?->name ?? '—' }}</td>
                        <td>{{ $o->artisan?->artisanProfile?->workshop_name ?? $o->artisan?->name ?? '—' }}</td>
                        <td class="text-end">₱{{ number_format($o->total, 2) }}</td>
                        <td><x-status-badge :status="$o->status" type="order" /></td>
                        <td>{{ $o->created_at?->format('M d, Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
