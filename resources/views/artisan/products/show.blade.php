@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('artisan.products.index') }}">My products</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 35) }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <h1 class="h2 fw-semibold mb-0">{{ $product->name }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('artisan.products.edit', $product) }}" class="btn btn-primary">Edit product</a>
            <a href="{{ route('artisan.products.index') }}" class="btn btn-outline-secondary">Back to list</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div id="productCarousel" class="carousel slide rounded-3 overflow-hidden border" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @foreach($product->images as $index => $image)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $image->image_url }}" class="d-block w-100" alt="{{ $product->name }}" style="max-height: 360px; object-fit: contain; background: #f8fafc;">
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
        </div>

        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header">Details</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Category:</strong> {{ $product->category->name ?? '—' }}</p>
                    <p class="mb-2"><strong>Price:</strong> ₱{{ number_format($product->price, 2) }}</p>
                    <p class="mb-2"><strong>Stock:</strong> {{ $product->stock }}</p>
                    <p class="mb-3"><strong>Status:</strong> <x-status-badge :status="$product->approval_status" type="product" /></p>
                    <hr>
                    <p class="mb-0"><strong>Description</strong></p>
                    <p class="text-body-secondary mb-0">{{ $product->description }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($product->approvals->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header">Approval history</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Reviewed by</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->approvals as $approval)
                                <tr>
                                    <td><x-status-badge :status="$approval->status" type="product" /></td>
                                    <td>{{ $approval->reviewer->name ?? '—' }}</td>
                                    <td>{{ $approval->reviewed_at ? $approval->reviewed_at->format('M d, Y') : '—' }}</td>
                                    <td>{{ $approval->notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
