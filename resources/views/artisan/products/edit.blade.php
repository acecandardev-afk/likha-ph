@extends('layouts.app')

@section('title', 'Edit: ' . $product->name)

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('artisan.products.index') }}">My products</a></li>
            <li class="breadcrumb-item"><a href="{{ route('artisan.products.show', $product) }}">{{ Str::limit($product->name, 25) }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Edit product</h1>

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('artisan.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Product name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $product->description) }}</textarea>
                            @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price (₱)</label>
                                <input type="number" name="price" id="price" step="0.01" min="1" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
                                @error('price')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" name="stock" id="stock" min="0" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', $product->stock) }}" required>
                                @error('stock')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Current images</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($product->images as $image)
                                    <div class="position-relative border rounded p-1">
                                        <img src="{{ $image->image_url }}" alt="" style="width: 80px; height: 80px; object-fit: cover;">
                                        <label class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white small text-center mb-0 py-1 cursor-pointer">
                                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="me-1"> Remove
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">At least one image must remain.</small>
                        </div>

                        <div class="mb-4">
                            <label for="new_images" class="form-label">Add more images (optional)</label>
                            <input type="file" name="new_images[]" id="new_images" class="form-control" accept="image/jpeg,image/jpg,image/png" multiple>
                            <small class="text-muted">Max 5 images total. JPEG or PNG, 2 MB each.</small>
                            @error('new_images')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save changes</button>
                            <a href="{{ route('artisan.products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
