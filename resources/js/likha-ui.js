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

/**
 * Scroll-linked parallax for [data-parallax-layer] inside [data-parallax-root].
 * Respects prefers-reduced-motion.
 */
export function initScrollParallax() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    if (!document.querySelector('[data-parallax-root]')) {
        return;
    }

    const layers = document.querySelectorAll('[data-parallax-layer]');
    if (!layers.length) {
        return;
    }

    let ticking = false;

    const update = () => {
        ticking = false;
        layers.forEach((layer) => {
            const root = layer.closest('[data-parallax-root]');
            if (!root) {
                return;
            }
            const rect = root.getBoundingClientRect();
            const vh = window.innerHeight || 1;
            if (rect.bottom < 0 || rect.top > vh) {
                layer.style.transform = '';
                return;
            }
            const strength = parseFloat(layer.getAttribute('data-parallax-strength') || '0.1');
            const centerDelta = (rect.top + rect.height / 2 - vh / 2) / vh;
            const s = Number.isFinite(strength) ? strength : 0.1;
            const ty = centerDelta * -52 * s * 10;
            layer.style.transform = `translate3d(0, ${ty}px, 0)`;
        });
    };

    const onScroll = () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
}

export function initLikhaUi() {
    initScrollReveal();
    initScrollParallax();
    initAuthOffcanvas();
}
