@extends('layouts.app')

@section('title', 'Settlement #'.$journal->id)

@section('content')
<div class="container py-2 py-md-3">
    <div class="mb-3">
        <a href="{{ route('admin.ledger.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to ledger</a>
    </div>
    <h1 class="h2 fw-semibold mb-1">Order {{ $journal->order?->order_number ?? '—' }}</h1>
    <p class="text-muted small mb-4">Posted {{ $journal->posted_at?->format('M j, Y g:i a') ?? '' }} · Double-entry lines for this delivery</p>

    @php $t = $journal->totalsBySide(); @endphp
    <div class="alert alert-{{ abs($t['debit'] - $t['credit']) > 0.02 ? 'danger' : 'success' }} small mb-4">
        <strong>Debits</strong> ₱{{ number_format($t['debit'], 2) }} &nbsp;·&nbsp;
        <strong>Credits</strong> ₱{{ number_format($t['credit'], 2) }}
        @if(abs($t['debit'] - $t['credit']) <= 0.02)
            — totals match for this settlement.
        @else
            — flagged for staff review (unexpected mismatch).
        @endif
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Side</th>
                            <th scope="col">What this line means</th>
                            <th class="text-end" scope="col">Amount (₱)</th>
                            <th scope="col" class="d-none d-md-table-cell">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->lines as $line)
                            <tr>
                                <td class="text-capitalize">{{ $line->side }}</td>
                                <td>{{ \App\Models\LedgerLine::labelForBucket($line->bucket) }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $line->amount, 2) }}</td>
                                <td class="small text-muted d-none d-md-table-cell">{{ $line->memo }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
