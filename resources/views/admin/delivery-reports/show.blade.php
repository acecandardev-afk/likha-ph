@extends('layouts.app')

@section('title', 'Delivery report')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.delivery-reports.index') }}">Delivery reports</a></li>
            <li class="breadcrumb-item active">#{{ $deliveryReport->id }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h1 class="h5 mb-0 fw-semibold">Report #{{ $deliveryReport->id }}</h1>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Concern:</strong> {{ $deliveryReport->concern }}</p>
                    @if($deliveryReport->details)
                        <p class="mb-0 small" style="white-space: pre-wrap;">{{ $deliveryReport->details }}</p>
                    @endif
                    @if($deliveryReport->proof_image_url)
                        <div class="mt-3">
                            <div class="small text-muted mb-1">Customer proof</div>
                            <img src="{{ $deliveryReport->proof_image_url }}" alt="Proof" class="img-fluid rounded border" style="max-width: 320px;">
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><strong>Context</strong></div>
                <div class="card-body small">
                    <p class="mb-1"><strong>Order:</strong> {{ $deliveryReport->orderPackage->order->order_number ?? '—' }}</p>
                    <p class="mb-1"><strong>Package:</strong> {{ $deliveryReport->orderPackage->sequence ?? '—' }}</p>
                    <p class="mb-1"><strong>Reporter:</strong> {{ $deliveryReport->reporter?->name ?? '—' }}</p>
                    <p class="mb-0"><strong>Rider:</strong> {{ $deliveryReport->orderPackage->rider?->full_name ?? '—' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Admin action</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.delivery-reports.update', $deliveryReport) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="{{ \App\Models\DeliveryReport::STATUS_OPEN }}" @selected($deliveryReport->status === \App\Models\DeliveryReport::STATUS_OPEN)>Open</option>
                                <option value="{{ \App\Models\DeliveryReport::STATUS_REVIEWED }}" @selected($deliveryReport->status === \App\Models\DeliveryReport::STATUS_REVIEWED)>Reviewed</option>
                                <option value="{{ \App\Models\DeliveryReport::STATUS_RESOLVED }}" @selected($deliveryReport->status === \App\Models\DeliveryReport::STATUS_RESOLVED)>Resolved</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin notes</label>
                            <textarea name="admin_notes" rows="3" class="form-control" placeholder="Internal notes">{{ old('admin_notes', $deliveryReport->admin_notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
