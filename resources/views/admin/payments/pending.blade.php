@extends('layouts.app')

@section('title', 'Pending Payments')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pending payments</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h2 fw-semibold mb-0">Pending payments</h1>
        <a href="{{ route('admin.payments.verified') }}" class="btn btn-outline-success btn-sm">Recorded</a>
    </div>

    @if($pendingPayments->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0 text-muted">No pending payments to verify.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Artisan</th>
                                <th>Amount</th>
                                <th>Submitted</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayments as $payment)
                                <tr>
                                    <td>{{ $payment->order?->order_number ?? '-' }}</td>
                                    <td>{{ $payment->order?->customer?->name ?? 'Unknown Customer' }}</td>
                                    <td>{{ $payment->order?->artisan?->artisanProfile?->workshop_name ?? $payment->order?->artisan?->name ?? 'Unknown Artisan' }}</td>
                                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->created_at?->format('M d, Y H:i') ?? '' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.payments.review', $payment) }}" class="btn btn-sm btn-primary">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $pendingPayments->links() }}</div>
    @endif
</div>
@endsection
