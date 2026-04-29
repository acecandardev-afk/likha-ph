@extends('layouts.app')

@section('title', 'Cash checks (riders)')

@section('content')
<div class="container py-3 py-lg-4">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cash checks</li>
        </ol>
    </nav>

    <h1 class="h3 fw-semibold mb-2">Office records vs rider reports</h1>
    <p class="small text-muted mb-4">Compare what riders say they turned in with what our payment records show for the same dates.</p>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.cod-treasury.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Posted from</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $from->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Posted to</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $to->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('admin.cod-treasury.index') }}" class="btn btn-outline-secondary">This month</a>
                </div>
            </form>
            @error('date_from')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('date_to')<div class="text-danger small">{{ $message }}</div>@enderror
            <p class="small text-muted mt-3 mb-0">
                Payment entries posted {{ $from->format('M j, Y') }}–{{ $to->format('M j, Y') }} ({{ number_format($journalCount) }} orders).
                Rider daily reports use the same calendar dates.
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100 border-primary border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">From payment records (official)</div>
                    <div class="fs-3 fw-bold">₱{{ number_format($ledgerCod, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Riders reported (total)</div>
                    <div class="fs-3 fw-bold">₱{{ number_format($remittanceDeclared, 2) }}</div>
                    <div class="small text-muted mt-2">Large gaps may need a quick follow-up with riders.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white"><strong>Breakdown (from records)</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Type</th><th class="text-end">Sum</th></tr></thead>
                    <tbody>
                        @forelse($bucketTotals as $bucket => $total)
                            <tr>
                                <td>{{ \App\Models\LedgerLine::labelForBucket($bucket) }}</td>
                                <td class="text-end font-monospace">₱{{ number_format((float) $total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-muted text-center py-4">No journals in range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white"><strong>Rider reports (sample)</strong></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light small"><tr><th>Date</th><th>Rider</th><th class="text-end">Cash reported</th></tr></thead>
                    <tbody>
                        @forelse($remittanceRows as $row)
                            <tr>
                                <td>{{ $row->report_date?->format('Y-m-d') }}</td>
                                <td>{{ $row->rider?->full_name ?? '—' }}</td>
                                <td class="text-end font-monospace">₱{{ number_format((float) $row->cod_declared_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted text-center py-4">No declarations in range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
