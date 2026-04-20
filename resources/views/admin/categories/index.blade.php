@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Categories</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Categories</h1>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0 fw-semibold">Add category</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-12 col-md-4">
                    <label for="name" class="form-label small">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="description" class="form-label small">Description</label>
                    <input type="text" name="description" id="description" class="form-control form-control-sm" value="{{ old('description') }}" maxlength="500" placeholder="Optional">
                </div>
                <div class="col-12 col-md-2">
                    <label for="icon" class="form-label small">Icon</label>
                    <input type="text" name="icon" id="icon" class="form-control form-control-sm" value="{{ old('icon') }}" maxlength="10" placeholder="e.g. bi-tag">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td><code class="small">{{ $category->slug }}</code></td>
                                <td><span class="text-muted small">{{ Str::limit($category->description, 40) }}</span></td>
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal-{{ $category->id }}">Edit</button>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Products must be moved first.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" @if($category->products_count > 0) disabled title="Cannot delete: has products" @endif>Delete</button>
                                    </form>
                                </td>
                            </tr>
                            {{-- Edit modal for this category --}}
                            <div class="modal fade" id="editCategoryModal-{{ $category->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="edit_name_{{ $category->id }}" class="form-label">Name</label>
                                                    <input type="text" name="name" id="edit_name_{{ $category->id }}" class="form-control" value="{{ $category->name }}" required maxlength="255">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_description_{{ $category->id }}" class="form-label">Description</label>
                                                    <input type="text" name="description" id="edit_description_{{ $category->id }}" class="form-control" value="{{ $category->description }}" maxlength="500">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="edit_icon_{{ $category->id }}" class="form-label">Icon</label>
                                                    <input type="text" name="icon" id="edit_icon_{{ $category->id }}" class="form-control" value="{{ $category->icon }}" maxlength="10">
                                                </div>
                                                <div class="mb-0">
                                                    <div class="form-check">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox" name="is_active" value="1" id="edit_is_active_{{ $category->id }}" class="form-check-input" {{ $category->is_active ? 'checked' : '' }}>
                                                        <label for="edit_is_active_{{ $category->id }}" class="form-check-label">Active</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No categories. Add one above.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
