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
        <div class="card-header d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <h5 class="mb-0 fw-semibold">Orders placed per day (last {{ $days }} days)</h5>
                <small class="text-muted">Line chart from the same daily counts shown in the summary table.</small>
            </div>
        </div>
        <div class="card-body">
            <script type="application/json" id="likha-insights-orders-data">@json(['labels' => $chartLabels, 'values' => $chartValues])</script>
            <div
                id="insights-chart-mount"
                class="rounded border bg-body-tertiary p-3"
                style="min-height: 300px;"
            >
                <canvas
                    id="likhaInsightsOrdersChart"
                    aria-describedby="insights-chart-table-desc"
                    aria-label="{{ __('Orders placed per day for the selected period.') }}"
                    role="img"
                ></canvas>
            </div>

            <p id="insights-chart-table-desc" class="visually-hidden">
                {{ __('Detailed day-by-day order counts appear in the table below.') }}
            </p>
            <details class="mt-4">
                <summary class="fw-medium text-body-secondary small">{{ __('Daily totals (table)') }}</summary>
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <caption class="visually-hidden">{{ __('Orders placed each day') }}</caption>
                        <thead class="table-light">
                            <tr>
                                <th scope="col">{{ __('Date') }}</th>
                                <th class="text-end" scope="col">{{ __('Orders') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordersByDay as $date => $count)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</td>
                                    <td class="text-end fw-semibold">{{ $count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
            <noscript>
                <div class="table-responsive mt-3">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th class="text-end">{{ __('Orders') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordersByDay as $date => $count)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</td>
                                    <td class="text-end">{{ $count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </noscript>
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
