@extends('layouts.app')

@section('title', 'Chats')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        @if(auth()->user()->isArtisan())
            <a href="{{ route('artisans.show', auth()->user()) }}" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to profile" aria-label="Back to profile">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif
        <h1 class="h4 fw-semibold mb-0"><i class="bi bi-chat-dots me-2"></i>Chats</h1>
    </div>

    @if($conversations->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-chat-dots display-4 text-muted mb-3"></i>
                <p class="text-muted mb-0">No conversations yet. Visit an artisan's profile and click <strong>Chat</strong> to start a conversation.</p>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="list-group list-group-flush">
                @foreach($conversations as $conv)
                    <a href="{{ route('chat.index', $conv['user']) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 text-decoration-none">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-dark">{{ $conv['name'] }}</div>
                            <div class="small text-muted text-truncate">{{ $conv['last_message'] ?? 'No messages' }}</div>
                        </div>
                        @if($conv['last_at'])
                            <div class="small text-muted">{{ $conv['last_at']->diffForHumans() }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
