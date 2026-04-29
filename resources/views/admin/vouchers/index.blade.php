@extends('layouts.app')

@section('title', 'Promo vouchers')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promo vouchers</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Promo vouchers</h1>
            <p class="text-muted small mb-0">Create codes buyers can enter at checkout. Codes are saved in uppercase.</p>
        </div>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New voucher</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Label</th>
                        <th>Discount</th>
                        <th>Min order</th>
                        <th>Redemptions</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td><code>{{ $voucher->code }}</code></td>
                            <td>{{ $voucher->label ?: '—' }}</td>
                            <td class="small">
                                @if($voucher->discount_type === 'fixed')
                                    ₱{{ number_format((float) $voucher->discount_value, 2) }} fixed
                                @else
                                    {{ rtrim(rtrim(number_format((float) $voucher->discount_value, 2), '0'), '.') }}%
                                    @if($voucher->maximum_discount_amount)
                                        <span class="text-muted">(cap ₱{{ number_format((float) $voucher->maximum_discount_amount, 2) }})</span>
                                    @endif
                                @endif
                            </td>
                            <td class="small">₱{{ number_format((float) $voucher->min_order_amount, 2) }}</td>
                            <td class="small">
                                {{ $voucher->times_redeemed }}
                                @if($voucher->max_redemptions !== null)
                                    / {{ $voucher->max_redemptions }}
                                @else
                                    <span class="text-muted">/ ∞</span>
                                @endif
                            </td>
                            <td>
                                @if($voucher->is_active)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.vouchers.destroy', $voucher) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete voucher {{ $voucher->code }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No vouchers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
            <div class="card-footer bg-white">{{ $vouchers->links() }}</div>
        @endif
    </div>
</div>
@endsection
