@extends('layouts.app')

@section('title', $rider->full_name.' — Rider profile')

@push('styles')
<style>
.rider-sales-hero {
    border-radius: 1rem;
    border: 1px solid rgba(45, 49, 66, 0.08);
    background: linear-gradient(145deg, rgba(59, 130, 246, 0.07) 0%, rgba(248, 250, 252, 1) 48%, rgba(255, 255, 255, 1) 100%);
}
.rider-sales-stat {
    border-radius: 0.85rem;
    border: 1px solid rgba(45, 49, 66, 0.08);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
@media (hover: hover) {
    .rider-sales-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(45, 49, 66, 0.07);
    }
}
.rider-sale-card {
    border-radius: 0.85rem;
    border: 1px solid rgba(45, 49, 66, 0.08);
}
.rider-sale-items {
    font-size: 0.875rem;
}
</style>
@endpush

@section('content')
<div class="container py-3 py-lg-4">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.riders.index') }}" class="text-decoration-none">Riders</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($rider->full_name, 40) }}</li>
        </ol>
    </nav>

    <div class="rider-sales-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-auto">
                @php
                    $parts = preg_split('/\s+/', trim((string) $rider->full_name)) ?: [];
                    $initials = '';
                    foreach (array_slice($parts, 0, 2) as $p) {
                        $initials .= strtoupper((string) Str::substr($p, 0, 1));
                    }
                    $initials = $initials ?: '?';
                @endphp
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fw-bold shadow-sm"
                     style="width: 84px; height: 84px; font-size: 1.35rem;" aria-hidden="true">
                    {{ $initials }}
                </div>
            </div>
            <div class="col">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h1 class="h3 fw-bold mb-0">{{ $rider->full_name }}</h1>
                    <x-status-badge :status="$rider->status" type="delivery" />
                </div>
                <p class="text-muted mb-2 small mb-lg-3 font-monospace">{{ $rider->rider_id }}</p>
                <div class="row row-cols-1 row-cols-sm-2 g-2 small">
                    <div><span class="text-muted">Phone</span><br><span class="fw-medium">{{ $rider->contact_number }}</span></div>
                    <div><span class="text-muted">Email</span><br><span class="fw-medium text-break">{{ $rider->email }}</span></div>
                    @if($rider->vehicle_type || $rider->vehicle_plate)
                        <div><span class="text-muted">Vehicle</span><br><span class="fw-medium">{{ $rider->vehicle_type ?? '—' }} @if($rider->vehicle_plate)<span class="text-muted">·</span> {{ $rider->vehicle_plate }}@endif</span></div>
                    @endif
                    @if($rider->address)
                        <div class="col-12"><span class="text-muted">Address</span><br><span class="fw-medium">{{ Str::limit($rider->address, 160) }}</span></div>
                    @endif
                </div>
            </div>
            <div class="col-12 col-lg-auto">
                <a href="{{ route('admin.riders.index') }}" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-arrow-left me-1"></i> Back to riders</a>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4 mt-2 mt-lg-0 ms-lg-2" data-bs-toggle="collapse" data-bs-target="#inline-edit-{{ $rider->id }}">
                    <i class="bi bi-pencil me-1"></i> Quick edit
                </button>
            </div>
        </div>

        <div class="collapse mt-4 pt-3 border-top border-white border-opacity-50" id="inline-edit-{{ $rider->id }}">
            <form method="POST" action="{{ route('admin.riders.update', $rider) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-md-3"><label class="form-label small mb-0">Name</label><input name="full_name" class="form-control form-control-sm" value="{{ $rider->full_name }}" required></div>
                <div class="col-md-2"><label class="form-label small mb-0">Contact</label><input name="contact_number" class="form-control form-control-sm" value="{{ $rider->contact_number }}" required></div>
                <div class="col-md-3"><label class="form-label small mb-0">Email</label><input type="email" name="email" class="form-control form-control-sm" value="{{ $rider->email }}" required></div>
                <div class="col-md-2"><label class="form-label small mb-0">Vehicle</label><input name="vehicle_type" class="form-control form-control-sm" value="{{ $rider->vehicle_type }}"></div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm" required>
                        <option value="available" @selected($rider->status === 'available')>Available</option>
                        <option value="busy" @selected($rider->status === 'busy')>Busy</option>
                        <option value="offline" @selected($rider->status === 'offline')>Offline</option>
                    </select>
                </div>
                <div class="col-md-10"><label class="form-label small mb-0">Address</label><input name="address" class="form-control form-control-sm" value="{{ $rider->address }}" placeholder="Address"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Save</button></div>
            </form>
        </div>
    </div>

    @php($feeHint = number_format((float) config('fees.rider_fee_per_package', 0), 2))

    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-md-4">
            <div class="rider-sales-stat bg-white p-4 h-100">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Deliveries completed</div>
                <div class="display-6 fw-bold text-dark lh-1">{{ number_format($stats['deliveries_count']) }}</div>
                <div class="small text-muted mt-2 mb-0">Packages marked delivered for this rider.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rider-sales-stat bg-white p-4 h-100">
                <div class="text-muted small text-uppercase fw-semibold mb-1">COD goods delivered</div>
                <div class="fs-2 fw-bold text-dark lh-sm">₱{{ number_format($stats['total_merchandise'], 2) }}</div>
                <div class="small text-muted mt-2 mb-0">Total item value in delivered packages (what customers paid for goods in those drops).</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rider-sales-stat bg-white p-4 h-100 border-primary border-opacity-25">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Rider fees recorded</div>
                <div class="fs-2 fw-bold text-primary lh-sm">₱{{ number_format($stats['total_rider_fees'], 2) }}</div>
                <div class="small text-muted mt-2 mb-0">Fixed when each package is marked delivered (currently ₱{{ $feeHint }} per stop).</div>
            </div>
        </div>
    </div>

    <div class="card rider-sale-card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h2 class="h5 fw-bold mb-1">Delivery history</h2>
            <p class="small text-muted mb-0">Each row is one package. Line items, merchandise total, exact delivered time, and rider fee for that stop.</p>
        </div>
        <div class="card-body p-4">
            @if($packages->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-truck fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-0">No completed deliveries yet for this rider.</p>
                </div>
            @else
                <div class="d-lg-none">
                    @foreach($packages as $pkg)
                        <div class="rider-sale-card bg-light bg-opacity-50 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <span class="fw-semibold">{{ $pkg->order->order_number ?? 'Order #'.$pkg->order_id }}</span>
                                    <span class="badge bg-secondary ms-1">Pkg {{ $pkg->sequence }}</span>
                                </div>
                                <span class="small text-muted text-end">{{ $pkg->deliveredAtLabel() }}</span>
                            </div>
                            @if($pkg->order->customer)
                                <div class="small text-muted mb-2">Customer: {{ $pkg->order->customer->name }}</div>
                            @endif
                            <ul class="list-unstyled rider-sale-items mb-2">
                                @foreach($pkg->items as $opi)
                                    @php($oi = $opi->orderItem)
                                    @if($oi)
                                        <li class="d-flex justify-content-between gap-2 py-1 border-bottom border-white">
                                            <span>{{ $oi->product_name ?? $oi->product?->name ?? 'Item' }}</span>
                                            <span class="text-nowrap">× {{ $opi->quantity }} · ₱{{ number_format((float) $oi->price * (int) $opi->quantity, 2) }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="text-muted">Package merchandise</span>
                                <span class="fw-bold">₱{{ number_format($pkg->merchandiseTotal(), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center small mt-1">
                                <span class="text-muted">Rider fee</span>
                                <span class="fw-semibold text-primary">₱{{ number_format((float) ($pkg->rider_fee_amount ?? 0), 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-none d-lg-block">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th>Order</th>
                                    <th>Items</th>
                                    <th class="text-end">Qty / unit price</th>
                                    <th class="text-end">Merch total</th>
                                    <th>Delivered at</th>
                                    <th class="text-end">Rider fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packages as $pkg)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $pkg->order->order_number ?? '#'.$pkg->order_id }}</span>
                                            <span class="badge bg-secondary ms-1">Pkg {{ $pkg->sequence }}</span>
                                            @if($pkg->order->customer)
                                                <div class="small text-muted">{{ $pkg->order->customer->name }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-unstyled rider-sale-items mb-0">
                                                @foreach($pkg->items as $opi)
                                                    @php($oi = $opi->orderItem)
                                                    @if($oi)
                                                        <li>{{ $oi->product_name ?? $oi->product?->name ?? 'Item' }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="text-end">
                                            @foreach($pkg->items as $opi)
                                                @php($oi = $opi->orderItem)
                                                @if($oi)
                                                    <div class="small">{{ $opi->quantity }} × ₱{{ number_format((float) $oi->price, 2) }}</div>
                                                @endif
                                            @endforeach
                                        </td>
                                        <td class="text-end fw-semibold">₱{{ number_format($pkg->merchandiseTotal(), 2) }}</td>
                                        <td style="min-width: 220px;"><span class="small">{{ $pkg->deliveredAtLabel() }}</span></td>
                                        <td class="text-end fw-semibold text-primary">₱{{ number_format((float) ($pkg->rider_fee_amount ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">{{ $packages->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
