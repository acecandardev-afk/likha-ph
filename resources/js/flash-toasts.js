/**
 * Floating toast stack + auto-dismiss (Bootstrap alerts).
 */

const DEFAULT_SUCCESS_MS = 3500;

function scheduleDismiss(el) {
    const raw = el.getAttribute('data-likha-auto-dismiss');
    const ms = raw ? parseInt(raw, 10) : DEFAULT_SUCCESS_MS;
    const duration = Number.isFinite(ms) && ms > 0 ? ms : DEFAULT_SUCCESS_MS;

    window.setTimeout(() => {
        try {
            if (window.bootstrap?.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(el).close();
            } else {
                el.remove();
            }
        } catch {
            el.remove();
        }
    }, duration);
}

export function initLikhaFlashToasts() {
    document.querySelectorAll('[data-likha-auto-dismiss]').forEach((el) => {
        scheduleDismiss(el);
    });
}

/**
 * Client-side toast (e.g. AJAX failures). Uses #likhaToastStack when present.
 */
export function showLikhaToast(message, options = {}) {
    const type = options.type ?? 'danger';
    const duration =
        options.duration ?? (type === 'success' || type === 'warning' ? DEFAULT_SUCCESS_MS : 6000);

    const stack = document.getElementById('likhaToastStack');
    if (!stack || typeof message !== 'string' || !message.trim()) {
        return;
    }

    const el = document.createElement('div');
    el.className = `alert alert-${type} alert-dismissible fade show likha-toast mb-0`;
    el.setAttribute('role', 'alert');
    el.setAttribute('data-likha-auto-dismiss', String(duration));

    const row = document.createElement('div');
    row.className = 'd-flex align-items-start gap-2';

    const text = document.createElement('div');
    text.className = 'flex-grow-1 min-w-0';
    text.textContent = message;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close flex-shrink-0 mt-0';
    btn.setAttribute('data-bs-dismiss', 'alert');
    btn.setAttribute('aria-label', 'Close');

    row.appendChild(text);
    row.appendChild(btn);
    el.appendChild(row);

    stack.appendChild(el);
    scheduleDismiss(el);
}
