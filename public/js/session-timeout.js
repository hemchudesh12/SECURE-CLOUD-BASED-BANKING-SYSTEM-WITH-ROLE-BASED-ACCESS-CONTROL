/**
 * session-timeout.js — 30-min timeout, 5-min warning.
 */
(function() {
    const WARNING_BEFORE = 5 * 60; // show warning 5 min before expiry
    const LOGOUT_URL     = '/banking-system/public/logout';
    const KEEPALIVE_URL  = '/banking-system/public/api/balance'; // any authenticated endpoint

    const warningEl   = document.getElementById('timeout-warning');
    const countdownEl = document.getElementById('session-countdown');
    const stayBtn     = document.getElementById('session-stay');
    const body        = document.body;

    let remaining = parseInt(body.dataset.sessionRemaining || '1800', 10);
    let warningShown = false;
    let interval;

    function formatTime(s) {
        const m = Math.floor(s / 60);
        const sec = s % 60;
        return `${m}:${sec.toString().padStart(2, '0')}`;
    }

    function tick() {
        remaining--;
        if (remaining <= 0) {
            clearInterval(interval);
            window.location.href = LOGOUT_URL + '?reason=timeout';
            return;
        }
        if (remaining <= WARNING_BEFORE && !warningShown) {
            warningShown = true;
            if (warningEl) warningEl.style.display = 'block';
        }
        if (warningShown && countdownEl) {
            countdownEl.textContent = formatTime(remaining);
        }
    }

    interval = setInterval(tick, 1000);

    if (stayBtn) {
        stayBtn.addEventListener('click', () => {
            fetch(KEEPALIVE_URL)
                .then(() => {
                    remaining = 1800;
                    warningShown = false;
                    if (warningEl) warningEl.style.display = 'none';
                })
                .catch(() => {});
        });
    }

    // Reset timer on user activity
    let activityTimer;
    ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt => {
        document.addEventListener(evt, () => {
            clearTimeout(activityTimer);
            activityTimer = setTimeout(() => {
                fetch(KEEPALIVE_URL).catch(() => {});
                remaining = 1800;
                warningShown = false;
                if (warningEl) warningEl.style.display = 'none';
            }, 60000); // debounce 1 min
        }, { passive: true });
    });
})();
