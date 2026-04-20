@extends('layouts.app')

@section('title', 'Add product')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('artisan.products.index') }}">My products</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add product</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Add product</h1>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <p class="text-body-secondary small mb-4">New products are submitted for approval. You’ll need at least one image (max 5).</p>

                    <form action="{{ route('artisan.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Product name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                            <small class="text-muted">At least 50 characters.</small>
                            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price (₱)</label>
                                <input type="number" name="price" id="price" step="0.01" min="1" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                                @error('price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" name="stock" id="stock" min="0" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" required>
                                @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="images" class="form-label">Product images</label>
                            <input type="file" name="images[]" id="images" class="form-control @error('images') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png" multiple required>
                            <small class="text-muted">At least 1, up to 5. JPEG or PNG, 2 MB each. First image is the main one.</small>
                            @error('images')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Submit for approval</button>
                            <a href="{{ route('artisan.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
