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

                    <div id="messagesContainer" class="order-messages-thread mb-4 px-1" style="max-height: 400px; overflow-y: auto;">
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

@push('styles')
<style>
    .order-messages-thread .order-msg-row {
        display: flex;
        width: 100%;
    }
    .order-messages-thread .order-msg-row--own {
        justify-content: flex-end;
    }
    .order-messages-thread .order-msg-row--other {
        justify-content: flex-start;
    }
    .order-messages-thread .order-msg-stack {
        max-width: min(85%, 22rem);
        min-width: 0;
    }
    .order-messages-thread .order-msg-bubble {
        border-radius: 1rem;
        padding: 0.5rem 0.75rem;
        word-wrap: break-word;
        white-space: pre-wrap;
        line-height: 1.4;
        font-size: 0.9375rem;
    }
    .order-messages-thread .order-msg-bubble--own {
        background: #1877f2;
        color: #fff;
    }
    .order-messages-thread .order-msg-bubble--other {
        background: #fff;
        color: #050505;
        border: 1px solid #e4e6eb;
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);
    }
    .order-messages-thread .order-msg-meta {
        font-size: 0.7rem;
        color: #65676b;
        margin-top: 0.2rem;
    }
    .order-messages-thread .order-msg-meta--own {
        text-align: right;
    }
    .order-messages-thread .order-msg-sender-line {
        font-size: 0.8rem;
        margin-bottom: 0.15rem;
    }
</style>
@endpush

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

        messages.forEach((msg, i) => {
            const prev = i > 0 ? messages[i - 1] : null;
            messagesContainer.appendChild(buildMessageNode(msg, prev));
        });
    }

    function buildMessageNode(msg, prevMsg) {
        const sameSender = prevMsg && Boolean(prevMsg.is_own) === Boolean(msg.is_own);
        const row = document.createElement('div');
        row.className = 'order-msg-row ' + (msg.is_own ? 'order-msg-row--own' : 'order-msg-row--other');
        row.dataset.own = msg.is_own ? '1' : '0';
        row.classList.add(sameSender ? 'mt-1' : 'mt-3');
        if (!prevMsg) {
            row.classList.remove('mt-3');
            row.classList.add('mt-0');
        }

        const stack = document.createElement('div');
        stack.className = 'order-msg-stack';

        if (msg.is_own) {
            stack.innerHTML = `
                <div class="order-msg-bubble order-msg-bubble--own">${escapeHtml(msg.message)}</div>
                <div class="order-msg-meta order-msg-meta--own">${escapeHtml(msg.created_at)}</div>
            `;
        } else {
            stack.innerHTML = `
                <div class="order-msg-sender-line d-flex justify-content-between align-items-baseline gap-2">
                    <span class="fw-semibold text-dark">${escapeHtml(msg.sender_name)}</span>
                    <span class="text-muted flex-shrink-0">${escapeHtml(msg.created_at)}</span>
                </div>
                <div class="order-msg-bubble order-msg-bubble--other">${escapeHtml(msg.message)}</div>
            `;
        }

        row.appendChild(stack);
        return row;
    }

    function appendMessage(msg) {
        const prevEl = messagesContainer.lastElementChild;
        let prevMsg = null;
        if (prevEl && prevEl.dataset.own !== undefined) {
            prevMsg = { is_own: prevEl.dataset.own === '1' };
        }
        messagesContainer.appendChild(buildMessageNode(msg, prevMsg));
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
