/**
 * Scroll reveal, auth offcanvas tabs, nav micro-interactions.
 */
export function initScrollReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;

    const show = (el) => el.classList.add('reveal--visible');

    if (!('IntersectionObserver' in window)) {
        els.forEach(show);
        return;
    }

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    show(entry.target);
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.06, rootMargin: '0px 0px -32px 0px' }
    );

    els.forEach((el) => io.observe(el));
}

function setAuthTab(panelRoot, name) {
    const panels = panelRoot.querySelectorAll('[data-auth-panel]');
    const buttons = panelRoot.querySelectorAll('[data-oc-auth-tab], .auth-tab-btn[data-auth-tab]');

    panels.forEach((p) => {
        const on = p.getAttribute('data-auth-panel') === name;
        p.classList.toggle('d-none', !on);
        if (on) p.removeAttribute('hidden');
        else p.setAttribute('hidden', '');
    });

    buttons.forEach((b) => {
        const on = b.getAttribute('data-auth-tab') === name;
        b.classList.toggle('active', on);
        b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
}

export function initAuthOffcanvas() {
    const panel = document.getElementById('likhaAuthPanel');
    if (!panel) return;

    const tabBtns = panel.querySelectorAll('[data-oc-auth-tab]');
    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            setAuthTab(panel, btn.getAttribute('data-auth-tab'));
        });
    });

    document.addEventListener('likha-auth-tab', (e) => {
        const tab = e.detail?.tab;
        if (tab === 'login' || tab === 'register') setAuthTab(panel, tab);
    });

    document.querySelectorAll('[data-open-auth]').forEach((el) => {
        el.addEventListener('click', (ev) => {
            const tab = el.getAttribute('data-open-auth') || 'login';
            const oc = window.bootstrap?.Offcanvas?.getOrCreateInstance(panel);
            if (oc) {
                oc.show();
                setTimeout(() => setAuthTab(panel, tab), 50);
            }
        });
    });

    document.querySelectorAll('[data-bs-toggle="offcanvas"][data-bs-target="#likhaAuthPanel"]').forEach((el) => {
        el.addEventListener('click', () => {
            const tab = el.getAttribute('data-auth-tab') || 'login';
            setTimeout(() => setAuthTab(panel, tab), 200);
        });
    });
}

export function initLikhaUi() {
    initScrollReveal();
    initAuthOffcanvas();
}
