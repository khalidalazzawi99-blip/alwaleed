(() => {
    'use strict';
    const init = () => {
        const dashboard = document.querySelector('.dashboard-motion');
        const toggle = dashboard?.querySelector('[data-dashboard-motion-toggle]');
        if (!dashboard || !toggle || dashboard.dataset.motionReady) return;
        dashboard.dataset.motionReady = 'true';
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        // Device settings are the default; an explicit choice takes precedence.
        const preferenceKey = 'alwaleed-dashboard-motion-choice';
        const validChoice = value => value === 'on' || value === 'off' ? value : null;
        let choice = null;
        try { choice = validChoice(localStorage.getItem(preferenceKey)); } catch (_) {}
        const cards = dashboard.querySelectorAll('.stat-box, .owner-stat, .mini-stat, .dashboard-card, .owner-card');
        cards.forEach((card, index) => card.style.setProperty('--motion-delay', `${Math.min(index * 65, 390)}ms`));
        const syncMotion = () => {
            const playing = choice ? choice === 'on' : !reducedMotion.matches;
            dashboard.dataset.motion = playing ? 'on' : 'off';
            toggle.hidden = false;
            const label = playing ? toggle.dataset.pauseLabel : toggle.dataset.resumeLabel;
            toggle.setAttribute('aria-label', label);
            toggle.title = label;
            toggle.querySelector('[data-motion-label]').textContent = label;
            const note = dashboard.querySelector('[data-motion-note]');
            note.hidden = !(choice === null && reducedMotion.matches);
            if (!playing) dashboard.classList.remove('motion-enter');
        };
        toggle.addEventListener('click', () => {
            choice = dashboard.dataset.motion === 'on' ? 'off' : 'on';
            try { localStorage.setItem(preferenceKey, choice); } catch (_) {}
            syncMotion();
        });
        const syncVisibility = () => dashboard.classList.toggle('motion-page-hidden', document.hidden);
        window.addEventListener('storage', event => {
            if (event.key === preferenceKey || event.key === null) {
                choice = validChoice(event.newValue);
                syncMotion();
            }
        });
        if (reducedMotion.addEventListener) reducedMotion.addEventListener('change', syncMotion);
        else reducedMotion.addListener(syncMotion);
        document.addEventListener('visibilitychange', syncVisibility);
        syncVisibility();
        syncMotion();
        if (dashboard.dataset.motion === 'on') {
            dashboard.classList.add('motion-enter');
            const lastCard = cards[cards.length - 1];
            lastCard?.addEventListener('animationend', event => {
                if (event.target === lastCard && event.animationName === 'dashboard-arrive') {
                    dashboard.classList.remove('motion-enter');
                }
            });
        }
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
