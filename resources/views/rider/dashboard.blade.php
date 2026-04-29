@extends('layouts.app')

@section('title', 'Rider dashboard')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h3 mb-3">Rider Dashboard</h1>

    @if($codTotals)
        <div class="row g-3 mb-3">
            <div class="col-12">
                <a href="{{ route('rider.cod-settlement') }}" class="card border-primary border-opacity-25 shadow-sm text-decoration-none text-reset h-100 rider-cod-summary">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div class="flex-grow-1">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Cash collected (all time)</div>
                            <div class="h3 fw-bold text-dark mb-1">₱{{ number_format($codTotals['cod_total'], 2) }}</div>
                            <div class="small text-muted mb-0">
                                For sellers <span class="text-success fw-semibold">₱{{ number_format($codTotals['seller_share'], 2) }}</span>
                                · Likha &amp; fees <span class="text-primary fw-semibold">₱{{ number_format($codTotals['company_side_total'], 2) }}</span>
                                · {{ number_format($codTotals['packages_count']) }} delivered stops
                            </div>
                        </div>
                        <span class="btn btn-primary btn-sm px-3">Pay-on-delivery details</span>
                    </div>
                </a>
            </div>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Active packages</div><div class="h4 mb-0">{{ $stats['assigned'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">Delivered</div><div class="h4 mb-0">{{ $stats['delivered'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted small">System pending assignments</div><div class="h4 mb-0">{{ $stats['pending_assignment'] }}</div></div></div></div>
    </div>
    <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">{{ $rider?->full_name ?? auth()->user()->name }}</div>
                <div class="text-muted small">Status: <x-status-badge :status="$rider?->status ?? 'offline'" type="delivery" /></div>
            </div>
            <a href="{{ route('rider.deliveries.index') }}" class="btn btn-primary">Open my deliveries</a>
        </div>
    </div>
</div>
@endsection
