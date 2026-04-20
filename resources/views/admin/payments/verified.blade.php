@extends('layouts.app')

@section('title', 'Verified Payments')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payments.pending') }}">Payments</a></li>
            <li class="breadcrumb-item active" aria-current="page">Verified</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Verified payments</h1>

    @if($verifiedPayments->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0 text-muted">No verified payments yet.</p>
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
                                <th>Verified at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($verifiedPayments as $payment)
                                <tr>
                                    <td>{{ $payment->order?->order_number ?? '-' }}</td>
                                    <td>{{ $payment->order?->customer?->name ?? 'Unknown Customer' }}</td>
                                    <td>{{ $payment->order?->artisan?->artisanProfile?->workshop_name ?? $payment->order?->artisan?->name ?? 'Unknown Artisan' }}</td>
                                    <td>₱{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->verified_at?->format('M d, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $verifiedPayments->links() }}</div>
    @endif
</div>
@endsection
