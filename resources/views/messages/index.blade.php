@extends('layouts.app')

@section('title', 'Order messages – #' . $order->order_number)

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            @if(auth()->user()->isCustomer())
                <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">My orders</a></li>
            @elseif(auth()->user()->isArtisan())
                <li class="breadcrumb-item"><a href="{{ route('artisan.orders.index') }}">Orders</a></li>
            @endif
            <li class="breadcrumb-item"><a href="{{ auth()->user()->isCustomer() ? route('customer.orders.show', $order) : route('artisan.orders.show', $order) }}">Order #{{ $order->order_number }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Messages</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Order #{{ $order->order_number }} – Messages</h5>
                    <a href="{{ auth()->user()->isCustomer() ? route('customer.orders.show', $order) : route('artisan.orders.show', $order) }}" class="btn btn-outline-secondary btn-sm">Back to order</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="mb-4" style="max-height: 400px; overflow-y: auto;">
                        @forelse($messages as $message)
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="fw-medium small">{{ $message->sender->name }}</span>
                                    <span class="text-muted small">{{ $message->created_at?->format('M d, Y H:i') ?? '' }}</span>
                                </div>
                                <div class="mt-1">{{ $message->message }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No messages yet. Send one below.</p>
                        @endforelse
                    </div>

                    <form action="{{ route('messages.store', $order) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label for="message" class="form-label small">Your message</label>
                            <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="3" placeholder="Type your message..." maxlength="1000" required>{{ old('message') }}</textarea>
                            @error('message')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send me-1"></i> Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
