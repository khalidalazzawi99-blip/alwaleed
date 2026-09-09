(() => {
    const dashboard = document.querySelector('.dashboard-motion');
    const toggle = dashboard?.querySelector('[data-dashboard-motion-toggle]');

    if (!dashboard || !toggle) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const preferenceKey = 'alwaleed-dashboard-motion';
    let enabled = true;

    try {
        enabled = localStorage.getItem(preferenceKey) !== 'off';
    } catch (_) {
        // Motion controls also work when browser storage is unavailable.
    }

    dashboard.querySelectorAll('.stat-box, .owner-stat, .mini-stat, .dashboard-card, .owner-card')
        .forEach((card, index) => {
            card.style.setProperty('--motion-delay', `${Math.min(index * 65, 390)}ms`);
        });

    const syncMotion = () => {
        const playing = enabled && !reducedMotion.matches;
        dashboard.dataset.motion = playing ? 'on' : 'off';
        toggle.hidden = reducedMotion.matches;
        const label = playing ? toggle.dataset.pauseLabel : toggle.dataset.resumeLabel;
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
    };

    toggle.addEventListener('click', () => {
        enabled = !enabled;
        try {
            localStorage.setItem(preferenceKey, enabled ? 'on' : 'off');
        } catch (_) {
            // Keep the choice for this page even if it cannot be saved.
        }
        syncMotion();
    });

    const syncVisibility = () => {
        dashboard.classList.toggle('motion-page-hidden', document.hidden);
    };

    reducedMotion.addEventListener('change', syncMotion);
    document.addEventListener('visibilitychange', syncVisibility);
    syncVisibility();
    syncMotion();
})();
