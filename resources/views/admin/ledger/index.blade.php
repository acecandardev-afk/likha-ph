@extends('layouts.app')

@section('title', 'Settlement ledger')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Settlement ledger</h1>
            <p class="text-muted small mb-0">Each row is recorded after delivery completes. Debit and credit totals match for every settlement.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
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
                            <th scope="col">Seller</th>
                            <th class="text-end" scope="col">Totals check</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $journal)
                            @php $t = $journal->totalsBySide(); @endphp
                            <tr>
                                <td class="small text-nowrap">{{ $journal->posted_at?->format('M j, Y g:i a') }}</td>
                                <td>
                                    <a href="{{ route('admin.ledger.show', $journal) }}" class="fw-semibold text-decoration-none">{{ $journal->order?->order_number ?? '—' }}</a>
                                </td>
                                <td class="small">{{ $journal->order?->customer?->name ?? '—' }}</td>
                                <td class="small">{{ $journal->order?->artisan?->name ?? '—' }}</td>
                                <td class="text-end small">
                                    ₱{{ number_format($t['debit'], 2) }} / ₱{{ number_format($t['credit'], 2) }}
                                    @if(abs($t['debit'] - $t['credit']) > 0.02)
                                        <span class="badge bg-danger ms-1">Uneven</span>
                                    @else
                                        <span class="text-success">✓</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nothing posted yet.</td>
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
