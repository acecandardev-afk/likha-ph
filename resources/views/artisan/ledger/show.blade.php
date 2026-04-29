@extends('layouts.app')

@section('title', 'Payment breakdown')

@section('content')
<div class="container py-2 py-md-3">
    <div class="mb-3">
        <a href="{{ route('artisan.ledger.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
    <h1 class="h2 fw-semibold mb-1">Order {{ $journal->order?->order_number ?? '—' }}</h1>
    <p class="text-muted small mb-4">Posted {{ $journal->posted_at?->format('M j, Y g:i a') ?? '' }}</p>

    @php $t = $journal->totalsBySide(); @endphp
    <p class="small text-muted mb-4">Look for “Seller share from items” — that’s what Likha owes you for items sold.</p>

    <div class="alert alert-{{ abs($t['debit'] - $t['credit']) > 0.02 ? 'warning' : 'light' }} small mb-4">
        Cash collected ₱{{ number_format($t['debit'], 2) }} · Cash allocated ₱{{ number_format($t['credit'], 2) }}
        @if(abs($t['debit'] - $t['credit']) <= 0.02)
            <span class="text-muted">— totals balance.</span>
        @else
            <span class="text-muted">— staff may review.</span>
        @endif
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Direction</th>
                            <th scope="col">Details</th>
                            <th class="text-end" scope="col">Amount (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->lines as $line)
                            <tr>
                                <td>{{ $line->side === 'debit' ? 'From buyer' : 'Paid out / owed' }}</td>
                                <td>{{ \App\Models\LedgerLine::labelForBucket($line->bucket) }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $line->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
