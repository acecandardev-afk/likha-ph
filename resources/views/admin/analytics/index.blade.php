@extends('layouts.app')

@section('title', 'Shop insights')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Shop insights</h1>
            <p class="text-muted small mb-0">Lightweight numbers to see how orders move and which items move fastest. They’re based on sign-ups and orders in this app — not third-party ad tracking.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card stat-card h-100 border-primary bg-primary bg-opacity-10">
                <div class="card-body p-3 p-md-4">
                    <p class="text-muted small fw-medium mb-1">Shoppers who placed an order</p>
                    <h2 class="h3 mb-1">{{ $activityRate }}%</h2>
                    <small class="text-muted">{{ $buyersWhoOrdered }} of {{ $buyerAccounts }} registered buyers have at least one order.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 fw-semibold">Orders placed per day (last {{ $days }} days)</h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($ordersByDay as $date => $count)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="border rounded p-2 text-center small">
                            <div class="text-muted">{{ \Carbon\Carbon::parse($date)->format('M j') }}</div>
                            <div class="fs-5 fw-semibold">{{ $count }}</div>
                            <span class="text-muted">orders</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 fw-semibold">Bestsellers by pieces sold</h5>
            <small class="text-muted">Rolling last 30 days</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Item</th>
                            <th class="text-end" scope="col">Pieces sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topItems as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td class="text-end fw-semibold">{{ number_format((int) $row->qty) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">No sales in the last 30 days yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
