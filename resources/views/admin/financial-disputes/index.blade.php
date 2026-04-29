@extends('layouts.app')

@section('title', 'Payment help requests')

@section('content')
<div class="container py-3 py-lg-4">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payment help</li>
        </ol>
    </nav>

    <h1 class="h3 fw-semibold mb-4">Customer payment &amp; delivery issues</h1>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light small">
                        <tr>
                            <th>Order</th>
                            <th>Raised by</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disputes as $dispute)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $dispute->order?->order_number ?? '—' }}</span>
                                    <div class="small text-muted">{{ Str::limit($dispute->description, 80) }}</div>
                                </td>
                                <td class="small">{{ $dispute->user?->name }} <span class="text-muted">({{ $dispute->actor_role }})</span></td>
                                <td><span class="badge bg-secondary">{{ $dispute->category }}</span></td>
                                <td><span class="badge bg-light text-dark border">{{ $dispute->status }}</span></td>
                                <td style="min-width: 260px;">
                                    <form method="POST" action="{{ route('admin.financial-disputes.resolve', $dispute) }}" class="row g-1 align-items-center">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-12">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="{{ \App\Models\OrderFinancialDispute::STATUS_UNDER_REVIEW }}" @selected($dispute->status === \App\Models\OrderFinancialDispute::STATUS_UNDER_REVIEW)>Under review</option>
                                                <option value="{{ \App\Models\OrderFinancialDispute::STATUS_RESOLVED }}" @selected($dispute->status === \App\Models\OrderFinancialDispute::STATUS_RESOLVED)>Resolved</option>
                                                <option value="{{ \App\Models\OrderFinancialDispute::STATUS_REJECTED }}" @selected($dispute->status === \App\Models\OrderFinancialDispute::STATUS_REJECTED)>Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="resolution_notes" class="form-control form-control-sm" rows="2" placeholder="Internal notes">{{ old('resolution_notes', $dispute->resolution_notes) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No disputes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($disputes->hasPages())
            <div class="card-footer">{{ $disputes->links() }}</div>
        @endif
    </div>
</div>
@endsection
