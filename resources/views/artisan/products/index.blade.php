@extends('layouts.app')

@section('title', 'My products')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-0">My products</h1>
            @if(filled($shopName ?? null))
                <p class="text-body-secondary small mb-0 mt-1">{{ $shopName }}</p>
            @endif
        </div>
        <a href="{{ route('artisan.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add product
        </a>
    </div>

    @if(request('status'))
        <div class="mb-3">
            <a href="{{ route('artisan.products.index') }}" class="btn btn-sm btn-outline-secondary">Clear filter</a>
        </div>
    @endif

    @if($products->isEmpty())
        <div class="alert alert-info">
            You don’t have any products yet. <a href="{{ route('artisan.products.create') }}" class="alert-link">Add your first product</a>.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $rowThumb = $product->primaryImage ?? $product->images->sortBy('sort_order')->first();
                                            @endphp
                                            @if($rowThumb)
                                                <img src="{{ $rowThumb->image_url }}" alt="" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                            @else
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                            <a href="{{ route('artisan.products.show', $product) }}" class="text-decoration-none fw-medium">{{ Str::limit($product->name, 40) }}</a>
                                        </div>
                                    </td>
                                    <td>{{ $product->category->name ?? '—' }}</td>
                                    <td>₱{{ number_format($product->price, 2) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td><x-status-badge :status="$product->approval_status" type="product" /></td>
                                    <td class="text-end">
                                        <a href="{{ route('artisan.products.show', $product) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('artisan.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProductModal" data-product-name="{{ e($product->name) }}" data-delete-url="{{ route('artisan.products.destroy', $product) }}">Delete</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $products->links() }}</div>
    @endif
</div>

{{-- Delete confirmation modal (centered, user-friendly) --}}
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 fw-semibold" id="deleteProductModalLabel">Delete product?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 text-body-secondary">You are about to delete <strong id="deleteProductName"></strong>. This cannot be undone.</p>
                <p class="small text-muted mt-2 mb-0">If this product has orders, deletion will be blocked.</p>
            </div>
            <div class="modal-footer border-0 pt-0 flex-nowrap">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteProductForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete product</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('deleteProductModal').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    var name = btn.getAttribute('data-product-name');
    var url = btn.getAttribute('data-delete-url');
    document.getElementById('deleteProductName').textContent = name || 'this product';
    document.getElementById('deleteProductForm').action = url;
});
</script>
@endpush
@endsection
