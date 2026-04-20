@extends('layouts.app')

@section('title', $artisan->artisanProfile->workshop_name ?? $artisan->name)

@section('content')
@php
    $workshopName = $artisan->artisanProfile->workshop_name ?? $artisan->name;
    $profile = $artisan->artisanProfile;
@endphp
<div class="container">
<div class="artisan-profile-page">
    {{-- Profile header --}}
    <div class="artisan-profile-header rounded-3 overflow-hidden mb-4 mb-md-5 shadow-sm border position-relative">
        <div class="d-flex gap-2 position-absolute top-0 end-0 m-2 m-md-3" style="z-index: 2;">
            @auth
                @if($artisan->id === auth()->id())
                    <a href="{{ route('chats.index') }}" class="btn btn-primary btn-sm rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span class="d-none d-sm-inline">Chats</span>
                    </a>
                @else
                    <button type="button" class="btn btn-primary btn-sm rounded-pill d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#chatModal" data-artisan-id="{{ $artisan->id }}" data-artisan-name="{{ $workshopName }}">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span class="d-none d-sm-inline">Chat</span>
                    </button>
                @endif
            @endauth
            @if(auth()->check() && auth()->user()->isCustomer())
                <a href="{{ route('customer.cart.index') }}" class="btn btn-light btn-sm rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-cart3"></i>
                    <span class="d-none d-sm-inline">View cart</span>
                </a>
            @endif
        </div>
        <div class="row g-0 align-items-stretch">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="artisan-profile-cover position-relative">
                    @if($profile && $profile->profile_image_url)
                        <img src="{{ $profile->profile_image_url }}" class="w-100 h-100" alt="{{ $workshopName }}" style="object-fit: cover; min-height: 280px;">
                    @else
                        <div class="artisan-profile-cover-placeholder w-100 h-100 d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-badge text-white opacity-75" style="font-size: 5rem;"></i>
                        </div>
                    @endif
                    <div class="artisan-profile-cover-overlay"></div>
                </div>
            </div>
            <div class="col-12 col-md-7 col-lg-8 bg-white d-flex align-items-center">
                <div class="p-4 p-md-5 w-100">
                    <p class="artisan-profile-badge mb-2 small fw-medium text-primary">Artisan</p>
                    <h1 class="artisan-profile-name mb-3">{{ $workshopName }}</h1>
                    @if($profile && $profile->full_location)
                        <p class="d-flex align-items-center text-body-secondary mb-2">
                            <i class="bi bi-geo-alt-fill me-2 text-primary"></i>{{ $profile->full_location }}
                        </p>
                    @endif
                    @if($profile && $profile->story)
                        <p class="artisan-profile-story text-body-secondary mb-0 mt-3">{{ $profile->story }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Products section --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
            <h2 class="h4 fw-semibold mb-0">Products by {{ $workshopName }}</h2>
            <span class="badge bg-light text-dark border px-3 py-2">{{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }}</span>
        </div>

        @if($products->isEmpty())
            <div class="artisan-empty-products text-center py-5 px-3 rounded-3">
                <i class="bi bi-box-seam display-4 text-muted mb-3 d-block"></i>
                <h3 class="h5 fw-semibold mb-2">No products yet</h3>
                <p class="text-body-secondary mb-0">This artisan doesn’t have any public products at the moment. Check back later.</p>
            </div>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                @foreach($products as $product)
                    <div class="col">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
            @if($products->hasPages())
                <div class="d-flex justify-content-center mt-5">{{ $products->links() }}</div>
            @endif
        @endif
</div>
</div>

@auth
{{-- Chat modal --}}
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true" data-chat-base-url="{{ url('/chat/with/' . $artisan->id) }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold d-flex align-items-center gap-2" id="chatModalLabel">
                    <i class="bi bi-chat-dots text-primary"></i>
                    <span id="chatModalTitle">Chat with {{ $workshopName }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="chatMessages" class="p-3 overflow-auto chat-messages">
                    <div id="chatMessagesLoading" class="text-center py-4 text-muted small">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span> Loading...
                    </div>
                    <div id="chatMessagesEmpty" class="text-center py-4 text-muted small d-none">No messages yet. Say hello!</div>
                </div>
                <div class="p-3 border-top bg-white">
                    <form id="chatForm" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" id="chatArtisanId" value="{{ $artisan->id }}">
                        <input type="text" id="chatMessageInput" class="form-control form-control-sm" placeholder="Type a message..." maxlength="1000" autocomplete="off" required>
                        <button type="submit" class="btn btn-primary btn-sm px-3" id="chatSendBtn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth

@push('styles')
<style>
.artisan-profile-header { border: 1px solid rgba(0,0,0,0.06); }
.artisan-profile-cart-btn {
    position: absolute;
    top: 0.85rem;
    right: 0.95rem;
    z-index: 2;
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.18);
}
.artisan-profile-cart-btn:hover {
    transform: translateY(-1px);
}
.artisan-profile-cover { min-height: 280px; }
.artisan-profile-cover-placeholder {
    background: linear-gradient(145deg, #E8A898 0%, #C45C3E 50%, #A84A2E 100%);
    min-height: 280px;
}
.artisan-profile-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, transparent 0%, rgba(255,255,255,0.97) 100%);
    pointer-events: none;
}
@media (max-width: 767.98px) {
    .artisan-profile-cover-overlay {
        background: linear-gradient(to bottom, transparent 0%, rgba(255,255,255,0.95) 100%);
    }
}
.artisan-profile-badge {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.artisan-profile-name {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a2e;
}
@media (min-width: 768px) {
    .artisan-profile-name { font-size: 2rem; }
}
.artisan-profile-story {
    font-size: 1rem;
    line-height: 1.65;
    max-width: 560px;
}
.artisan-empty-products {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px dashed #cbd5e1;
}
</style>
@endpush

@auth
@endauth
@endsection
