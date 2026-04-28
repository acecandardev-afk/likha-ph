@extends('layouts.app')

@section('title', 'Rider dashboard')

@section('content')
<div class="container py-2 py-md-3">
    <h1 class="h3 mb-3">Rider Dashboard</h1>
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
