function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderMessage(msg) {
    const div = document.createElement('div');
    div.className = 'mb-2 chat-msg ' + (msg.is_mine ? 'mine' : '');
    div.innerHTML =
        '<div class="rounded-3 px-3 py-2 d-inline-block ' +
        (msg.is_mine ? 'bg-primary text-white' : 'bg-white border') +
        '">' +
        '<div class="small fw-medium opacity-75">' +
        (msg.is_mine ? 'You' : escapeHtml(msg.sender_name)) +
        '</div>' +
        '<div class="small">' + escapeHtml(msg.message) + '</div>' +
        '<div class="small opacity-75 chat-meta">' + msg.created_at + '</div></div>';
    return div;
}

function initChatFlow(config) {
    const container = document.getElementById(config.messagesId);
    const loading = document.getElementById(config.loadingId);
    const empty = document.getElementById(config.emptyId);
    const form = document.getElementById(config.formId);
    const input = document.getElementById(config.inputId);
    const sendBtn = document.getElementById(config.sendBtnId);
    if (!container || !loading || !empty || !form || !input || !sendBtn) return;

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content ||
        document.querySelector(`#${config.formId} input[name="_token"]`)?.value;
    let pollInterval = null;
    let lastCount = 0;

    function loadMessages(showLoading = true) {
        if (showLoading) {
            container.querySelectorAll('.chat-msg').forEach(el => el.remove());
            loading.classList.remove('d-none');
            empty.classList.add('d-none');
        }

        fetch(config.baseUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                loading.classList.add('d-none');
                const messages = data.messages || [];
                if (showLoading || messages.length !== lastCount) {
                    lastCount = messages.length;
                    container.querySelectorAll('.chat-msg').forEach(el => el.remove());
                    if (messages.length > 0) {
                        messages.forEach(msg => container.insertBefore(renderMessage(msg), loading));
                        empty.classList.add('d-none');
                    } else {
                        empty.classList.remove('d-none');
                    }
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(() => {
                if (showLoading) {
                    loading.classList.add('d-none');
                    empty.textContent = 'Failed to load messages.';
                    empty.classList.remove('d-none');
                }
            });
    }

    function startPolling() {
        loadMessages(true);
        pollInterval = setInterval(() => loadMessages(false), config.pollMs ?? 3000);
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;

        sendBtn.disabled = true;
        fetch(config.baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ message: msg }),
        })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    empty.classList.add('d-none');
                    container.appendChild(renderMessage(data.message));
                    container.scrollTop = container.scrollHeight;
                }
                input.value = '';
            })
            .catch(() => {})
            .finally(() => {
                sendBtn.disabled = false;
            });
    });

    return { startPolling, stopPolling, loadMessages };
}

export function initChatPage() {
    const chatPage = document.querySelector('[data-chat-page="true"]');
    if (!chatPage) return;
    const baseUrl = chatPage.getAttribute('data-chat-base-url');
    if (!baseUrl) return;

    const flow = initChatFlow({
        baseUrl,
        messagesId: 'chatMessages',
        loadingId: 'chatMessagesLoading',
        emptyId: 'chatMessagesEmpty',
        formId: 'chatForm',
        inputId: 'chatMessageInput',
        sendBtnId: 'chatSendBtn',
    });
    if (!flow) return;

    flow.startPolling();
    window.addEventListener('beforeunload', () => flow.stopPolling());
}

export function initChatModal() {
    const chatModal = document.getElementById('chatModal');
    if (!chatModal) return;
    const baseUrl = chatModal.getAttribute('data-chat-base-url');
    if (!baseUrl) return;

    const flow = initChatFlow({
        baseUrl,
        messagesId: 'chatMessages',
        loadingId: 'chatMessagesLoading',
        emptyId: 'chatMessagesEmpty',
        formId: 'chatForm',
        inputId: 'chatMessageInput',
        sendBtnId: 'chatSendBtn',
    });
    if (!flow) return;

    chatModal.addEventListener('shown.bs.modal', () => flow.startPolling());
    chatModal.addEventListener('hidden.bs.modal', () => flow.stopPolling());
}
