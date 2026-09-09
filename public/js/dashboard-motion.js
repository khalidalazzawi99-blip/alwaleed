(() => {
    'use strict';

    const dashboard = document.querySelector('.dashboard-motion');
    const toggle = dashboard?.querySelector('[data-dashboard-motion-toggle]');
    if (!dashboard || !toggle) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const preferenceKey = 'alwaleed-dashboard-motion';
    let enabled = true;
    try {
        enabled = localStorage.getItem(preferenceKey) !== 'off';
    } catch (_) {
        // The control remains usable when storage is blocked.
    }

    const cards = dashboard.querySelectorAll('.stat-box, .owner-stat, .mini-stat, .dashboard-card, .owner-card');
    cards.forEach((card, index) => {
        card.style.setProperty('--motion-delay', `${Math.min(index * 65, 390)}ms`);
    });

    const syncMotion = () => {
        const playing = enabled && !reducedMotion.matches;
        dashboard.dataset.motion = playing ? 'on' : 'off';
        toggle.hidden = false;
        toggle.disabled = reducedMotion.matches;
        const label = reducedMotion.matches ? toggle.dataset.reducedLabel
            : playing ? toggle.dataset.pauseLabel : toggle.dataset.resumeLabel;
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
        // Reveal immediately if motion is disabled during the entrance.
        if (!playing) dashboard.classList.remove('motion-enter');
    };

    toggle.addEventListener('click', () => {
        enabled = !enabled;
        try {
            localStorage.setItem(preferenceKey, enabled ? 'on' : 'off');
        } catch (_) {
            // Preserve the in-page choice even without persistent storage.
        }
        syncMotion();
    });

    const syncVisibility = () => {
        dashboard.classList.toggle('motion-page-hidden', document.hidden);
    };

    window.addEventListener('storage', (event) => {
        if (event.key === preferenceKey || event.key === null) {
            enabled = event.newValue !== 'off';
            syncMotion();
        }
    });
    reducedMotion.addEventListener('change', syncMotion);
    document.addEventListener('visibilitychange', syncVisibility);
    syncVisibility();
    syncMotion();

    // Entrance runs only on initial load, never again when toggling motion.
    if (dashboard.dataset.motion === 'on') {
        dashboard.classList.add('motion-enter');
        const lastCard = cards[cards.length - 1];
        lastCard?.addEventListener('animationend', (event) => {
            if (event.target === lastCard && event.animationName === 'dashboard-arrive') {
                dashboard.classList.remove('motion-enter');
            }
        });
    }
})();
