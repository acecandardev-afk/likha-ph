@extends('layouts.app')

@section('title', 'Write a review')

@section('content')
<div class="container py-2 py-md-3">
    <div class="mb-3">
        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to order</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 fw-semibold">Review for {{ $product->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customer.reviews.store', ['order' => $order, 'product' => $product]) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="">Select rating</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Your review (optional)</label>
                            <textarea name="comment" rows="4" class="form-control" placeholder="Share your experience with this product"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
