@extends('layouts.app')

@section('title', 'Report delivery issue')

@section('content')
<div class="container py-2 py-md-3" style="max-width: 640px;">
    <a href="{{ route('customer.orders.show', $orderPackage->order) }}" class="btn btn-outline-secondary btn-sm mb-3">Back to order</a>

    <h1 class="h5 fw-semibold mb-2">Report a delivery concern</h1>
    <p class="text-muted small mb-4">Order {{ $orderPackage->order->order_number }} · Package {{ $orderPackage->sequence }}</p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('customer.delivery-reports.store', $orderPackage) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="concern" class="form-label">Summary <span class="text-danger">*</span></label>
                    <input type="text" name="concern" id="concern" class="form-control @error('concern') is-invalid @enderror" value="{{ old('concern') }}" required maxlength="120" placeholder="Short description">
                    @error('concern')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="details" class="form-label">Details</label>
                    <textarea name="details" id="details" rows="4" class="form-control @error('details') is-invalid @enderror" placeholder="What happened?">{{ old('details') }}</textarea>
                    @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="proof_image" class="form-label">Photo proof (optional)</label>
                    <input type="file" name="proof_image" id="proof_image" class="form-control @error('proof_image') is-invalid @enderror" accept="image/*">
                    @error('proof_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Submit report</button>
            </form>
        </div>
    </div>
</div>
@endsection
