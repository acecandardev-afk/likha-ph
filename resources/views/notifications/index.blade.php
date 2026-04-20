@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notifications</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h2 fw-semibold mb-0">Notifications</h1>
    </div>

    @if($notifications->isEmpty())
        <div class="alert alert-info mb-0">
            You have no notifications yet.
        </div>
    @else
        <div class="card border shadow-sm likha-notifications-card overflow-hidden">
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item list-group-item-action px-3 px-md-4 py-3 likha-notification-row {{ $notification->is_read ? '' : 'likha-notification-row--unread' }}">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold {{ $notification->is_read ? '' : 'text-body' }}">{{ $notification->title }}</div>
                                @if($notification->body)
                                    <div class="small text-body-secondary mt-1">{{ $notification->body }}</div>
                                @endif
                                @if(!empty($notification->action_url))
                                    <a href="{{ $notification->action_url }}" class="btn btn-sm btn-primary mt-2">Open</a>
                                @endif
                            </div>
                            <time class="small text-body-secondary text-nowrap flex-shrink-0" datetime="{{ $notification->created_at?->toIso8601String() }}">
                                {{ $notification->created_at?->format('M d, Y H:i') ?? '' }}
                            </time>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

