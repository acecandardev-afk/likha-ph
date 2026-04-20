@extends('layouts.app')

@section('title', 'Receipt ' . $sale->receipt_number)

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $sale->receipt_number }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h3 fw-semibold mb-0">Receipt {{ $sale->receipt_number }}</h1>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary btn-sm">Back to sales</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12 col-md-6">
                    <p class="mb-1"><strong>Date:</strong> {{ $sale->created_at?->format('M d, Y H:i') ?? '' }}</p>
                    <p class="mb-1"><strong>Cashier:</strong> {{ $sale->user->name ?? '—' }}</p>
                    <p class="mb-0"><strong>Method:</strong> {{ strtoupper($sale->payment_method) }}</p>
                </div>
            </div>

            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Unit price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">₱{{ number_format($sale->total_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Amount paid</th>
                            <th class="text-end">₱{{ number_format($sale->amount_paid, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Change</th>
                            <th class="text-end">₱{{ number_format($sale->change_amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
