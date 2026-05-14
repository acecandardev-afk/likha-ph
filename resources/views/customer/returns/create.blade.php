@extends('layouts.app')

@section('title', 'Request return')

@section('content')
<div class="container py-2 py-md-3" style="max-width: 640px;">
    <x-profile-header-nav active="returns" />
    <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm mb-3">Back to order</a>

    <h1 class="h5 fw-semibold mb-2">Request a return</h1>
    <p class="text-muted small mb-1">Order {{ $order->order_number }}</p>
    <p class="fw-semibold mb-4">{{ $orderItem->product?->name ?? $orderItem->product_name }}</p>

    <div class="alert alert-light border small mb-4">
        Returns are reviewed by admin. Please upload a clear photo showing the issue. You may return up to <strong>{{ $returnable }}</strong> unit(s) for this line.
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('customer.orders.items.returns.store', [$order, $orderItem]) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity to return <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1" max="{{ $returnable }}" value="{{ old('quantity', $returnable) }}" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                    <select name="reason" id="reason" class="form-select @error('reason') is-invalid @enderror" required>
                        <option value="" disabled @selected(! old('reason'))>Choose…</option>
                        @foreach(\App\Models\OrderItemReturn::reasonLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Details <span class="text-danger">*</span></label>
                    <textarea name="notes" id="notes" rows="5" class="form-control @error('notes') is-invalid @enderror" required minlength="10" maxlength="5000" placeholder="Describe what is wrong (at least 10 characters).">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="proof_image" class="form-label">Photo proof <span class="text-danger">*</span></label>
                    <input type="file" name="proof_image" id="proof_image" class="form-control @error('proof_image') is-invalid @enderror" accept="image/*" required>
                    @error('proof_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">JPEG, PNG, or WebP. Max 5 MB.</div>
                </div>
                <button type="submit" class="btn btn-warning text-dark fw-semibold">Submit return request</button>
            </form>
        </div>
    </div>
</div>
@endsection
