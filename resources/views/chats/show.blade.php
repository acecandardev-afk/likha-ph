@extends('layouts.app')

@section('title', 'Chat with ' . $otherName)

@section('content')
<div class="container py-4 chat-shell" data-chat-page="true" data-chat-base-url="{{ url('/chat/with/' . $user->id) }}">
    <div class="card border-0 shadow-sm chat-card">
        <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2">
            <a href="{{ route('chats.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center chat-back-btn" title="Back to chats" aria-label="Back to chats">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h5 mb-0 fw-semibold flex-grow-1"><i class="bi bi-chat-dots text-primary me-2"></i>{{ $otherName }}</h2>
        </div>
        <div id="chatMessages" class="card-body p-3 overflow-auto border-bottom chat-messages">
            <div id="chatMessagesLoading" class="text-center py-4 text-muted small">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span> Loading...
            </div>
            <div id="chatMessagesEmpty" class="text-center py-4 text-muted small d-none">No messages yet. Say hello!</div>
        </div>
        <div class="card-footer bg-white border-0 p-3">
            <form id="chatForm" class="d-flex gap-2">
                @csrf
                <input type="text" id="chatMessageInput" class="form-control" placeholder="Type a message..." maxlength="1000" autocomplete="off" required>
                <button type="submit" class="btn btn-primary px-4" id="chatSendBtn">
                    <i class="bi bi-send-fill"></i> Send
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
