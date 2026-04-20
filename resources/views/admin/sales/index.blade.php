@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sales</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h2 fw-semibold mb-0">Sales</h1>
        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> New sale
        </a>
    </div>

    @if($sales->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0 text-muted">No sales recorded yet.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Cashier</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Change</th>
                                <th>Method</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->receipt_number }}</td>
                                    <td>{{ $sale->created_at?->format('M d, Y H:i') ?? '' }}</td>
                                    <td>{{ $sale->user->name ?? '—' }}</td>
                                    <td>{{ $sale->items->sum('quantity') }}</td>
                                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                                    <td>₱{{ number_format($sale->amount_paid, 2) }}</td>
                                    <td>₱{{ number_format($sale->change_amount, 2) }}</td>
                                    <td>{{ strtoupper($sale->payment_method) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $sales->links() }}</div>
    @endif
</div>
@endsection
