@extends('layouts.app')

@section('title', 'Rejected Products')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.products.pending') }}">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rejected</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Rejected products</h1>

    @if($rejectedProducts->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-x-circle text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0 text-muted">No rejected products.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Artisan</th>
                                <th>Category</th>
                                <th>Reason</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rejectedProducts as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($product->images->isNotEmpty())
                                                <img src="{{ $product->images->first()->image_url }}" alt="" class="rounded" width="48" height="48" style="object-fit: cover;">
                                            @else
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                            <span>{{ Str::limit($product->name, 35) }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $product->artisan?->artisanProfile?->workshop_name ?? $product->artisan?->name ?? 'Unknown Artisan' }}</td>
                                    <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                                    <td><span class="text-muted small">{{ Str::limit($product->rejection_reason, 40) }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.products.review', $product) }}" class="btn btn-sm btn-primary">Review again</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $rejectedProducts->links() }}</div>
    @endif
</div>
@endsection
