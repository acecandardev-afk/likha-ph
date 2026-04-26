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

                    <div id="messagesContainer" class="mb-4" style="max-height: 400px; overflow-y: auto;">
                        <!-- Messages will be loaded here via AJAX -->
                    </div>

                    <form id="messageForm">
                        @csrf
                        <div class="mb-2">
                            <label for="message" class="form-label small">Your message</label>
                            <textarea name="message" id="message" class="form-control" rows="3" placeholder="Type your message..." maxlength="1000" required></textarea>
                            <small class="text-danger" id="messageError" style="display: none;"></small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" id="sendBtn">
                            <i class="bi bi-send me-1"></i> Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderKey = @json($order->order_number);
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('message');
    const messagesContainer = document.getElementById('messagesContainer');
    const sendBtn = document.getElementById('sendBtn');
    let lastMessageId = 0;
    let pollingInterval;

    // Load initial messages
    loadAllMessages();

    // Start polling for new messages every 2 seconds
    pollingInterval = setInterval(loadMessages, 2000);

    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        if (!message) return;

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        fetch(`/orders/${encodeURIComponent(orderKey)}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                if (data.message) {
                    appendMessage(data.message);
                }
                loadMessages();
            } else {
                alert('Error sending message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending message');
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send me-1"></i> Send';
        });
    });

    function loadMessages() {
        fetch(`/orders/${encodeURIComponent(orderKey)}/messages/fetch?since_id=${encodeURIComponent(lastMessageId)}`)
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    appendMessages(data.messages);
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
    }

    function loadAllMessages() {
        fetch(`/orders/${encodeURIComponent(orderKey)}/messages/fetch`)
            .then(response => response.json())
            .then(data => {
                renderMessages(data.messages);
                if ((data.messages || []).length > 0) {
                    lastMessageId = Math.max(...data.messages.map(m => Number(m.id || 0)));
                }
                // Auto-scroll to bottom
                setTimeout(() => {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }, 100);
            })
            .catch(error => console.error('Error loading messages:', error));
    }

    function renderMessages(messages) {
        messagesContainer.innerHTML = '';

        if (messages.length === 0) {
            messagesContainer.innerHTML = '<p class="text-muted mb-0">No messages yet. Send one below.</p>';
            return;
        }

        messages.forEach(msg => {
            messagesContainer.appendChild(buildMessageNode(msg));
        });
    }

    function buildMessageNode(msg) {
        const div = document.createElement('div');
        div.className = 'mb-3 pb-3 border-bottom';

        const alignment = msg.is_own ? 'text-end' : '';
        const bgColor = msg.is_own ? 'bg-primary bg-opacity-10' : '';

        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-2 ${alignment}">
                <span class="fw-medium small">${escapeHtml(msg.sender_name)}</span>
                <span class="text-muted small">${msg.created_at}</span>
            </div>
            <div class="mt-1 ${bgColor} p-2 rounded">${escapeHtml(msg.message)}</div>
        `;

        return div;
    }

    function appendMessage(msg) {
        messagesContainer.appendChild(buildMessageNode(msg));
        lastMessageId = Math.max(lastMessageId, Number(msg.id || 0));
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function appendMessages(messages) {
        if (!messages || messages.length === 0) {
            return;
        }

        if (messagesContainer.textContent.includes('No messages yet')) {
            messagesContainer.innerHTML = '';
        }

        messages.forEach(appendMessage);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        clearInterval(pollingInterval);
    });
});
</script>
@endpush

@endsection
