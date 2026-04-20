@extends('layouts.app')

@section('title', 'Review: ' . $product->name)

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.products.pending') }}">Pending products</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            @if($product->images->isNotEmpty())
                <div id="productCarousel" class="carousel slide rounded-3 overflow-hidden border" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($product->images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ $image->image_url }}" class="d-block w-100" alt="{{ $product->name }}" style="max-height: 400px; object-fit: contain; background: #f8fafc;">
                            </div>
                        @endforeach
                    </div>
                    @if($product->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center" style="min-height: 280px;">
                    <span class="text-muted">No image</span>
                </div>
            @endif
        </div>

        <div class="col-12 col-md-6">
            <h1 class="h3 fw-semibold mb-2">{{ $product->name }}</h1>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-secondary">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                <span class="badge bg-warning">Pending approval</span>
                @if($product->stock > 0)
                    <span class="badge bg-success">Stock: {{ $product->stock }}</span>
                @else
                    <span class="badge bg-danger">Out of stock</span>
                @endif
            </div>
            <h2 class="h4 text-primary mb-3">₱{{ number_format($product->price, 2) }}</h2>

            <p class="text-body-secondary mb-4">{{ $product->description }}</p>

            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title fw-semibold mb-2">Artisan</h6>
                    <div class="d-flex align-items-center">
                        @if($product->artisan?->artisanProfile?->profile_image)
                            <img src="{{ $product->artisan?->artisanProfile?->profile_image_url }}" class="rounded-circle me-3" width="50" height="50" alt="{{ $product->artisan?->name ?? 'Unknown Artisan' }}">
                        @endif
                        <div>
                            <strong>{{ $product->artisan?->name ?? 'Unknown Artisan' }}</strong><br>
                            <small class="text-muted">{{ $product->artisan?->artisanProfile?->workshop_name ?? '—' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approve / Reject actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-semibold">Review decision</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label small">Notes (optional)</label>
                        <textarea form="approveForm" name="notes" id="approve_notes" rows="2" class="form-control form-control-sm" placeholder="Optional notes for the artisan">{{ old('notes') }}</textarea>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <form id="approveForm" action="{{ route('admin.products.approve', $product) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectProductModal">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                        <a href="{{ route('admin.products.pending') }}" class="btn btn-outline-secondary">Back to pending</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Reject product confirmation modal --}}
<div class="modal fade" id="rejectProductModal" tabindex="-1" aria-labelledby="rejectProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="rejectProductModalLabel">Reject product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <p class="text-muted mb-3">Are you sure you want to reject "{{ $product->name }}"? Please provide a reason for the artisan.</p>
                <form action="{{ route('admin.products.reject', $product) }}" method="POST" id="rejectProductForm">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label fw-medium">Rejection reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reject_reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="Explain why this product cannot be approved..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Reject product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($errors->has('reason'))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('rejectProductModal'));
    modal.show();
});
</script>
@endpush
@endif
@endsection
