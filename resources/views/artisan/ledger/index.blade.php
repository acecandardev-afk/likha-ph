@extends('layouts.app')

@section('title', 'Payment records')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Your payment records</h1>
            <p class="text-muted small mb-0">Official breakdown after delivery — same numbers the team uses.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('artisan.earnings.index') }}" class="btn btn-outline-secondary btn-sm">After delivery totals</a>
            <a href="{{ route('artisan.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Posted</th>
                            <th scope="col">Order</th>
                            <th scope="col">Buyer</th>
                            <th class="text-end" scope="col">Cash in · Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $journal)
                            @php $t = $journal->totalsBySide(); @endphp
                            <tr>
                                <td class="small text-nowrap">{{ $journal->posted_at?->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('artisan.ledger.show', $journal) }}" class="fw-semibold text-decoration-none">{{ $journal->order?->order_number ?? '—' }}</a>
                                </td>
                                <td class="small">{{ $journal->order?->customer?->name ?? '—' }}</td>
                                <td class="text-end small">₱{{ number_format($t['debit'], 2) }} / ₱{{ number_format($t['credit'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No payment records yet — they appear after delivery is complete.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($journals->hasPages())
            <div class="card-footer">{{ $journals->links() }}</div>
        @endif
    </div>
</div>
@endsection
