<script>
(() => {
    const timeoutMs = @json($timeoutMs ?? ((int) config('session.lifetime', 45) * 60 * 1000));
    const loginUrl = @json($loginUrl ?? route('login'));
    const logoutUrl = @json($logoutUrl ?? route('logout'));
    const csrfToken = @json(csrf_token());
    const checkUrl = @json(route('session.check'));
    const storageKey = 'anggota_last_activity_at';

    if (!timeoutMs || timeoutMs <= 0) return;

    let expiring = false;

    const now = () => Date.now();
    const getLastAct = () => Number(localStorage.getItem(storageKey) || 0) || 0;
    const setLastAct = () => localStorage.setItem(storageKey, String(now()));
    const isExpired = () => {
        const lastActivity = getLastAct();
        return lastActivity > 0 && (now() - lastActivity) >= timeoutMs;
    };

    const doLogout = () => {
        if (expiring) return;

        expiring = true;
        localStorage.removeItem(storageKey);

        try {
            if (navigator.sendBeacon) {
                const body = new Blob([JSON.stringify({ _token: csrfToken })], { type: 'application/json' });
                navigator.sendBeacon(logoutUrl, body);
            } else {
                fetch(logoutUrl, {
                    method: 'POST',
                    keepalive: true,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
            }
        } catch (_) {}

        window.location.replace(loginUrl);
    };

    const checkServer = async () => {
        if (expiring) return;

        try {
            const response = await fetch(checkUrl, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                doLogout();
            }
        } catch (_) {}
    };

    const checkLocal = () => {
        if (!expiring && isExpired()) {
            doLogout();
        }
    };

    const markActivity = () => {
        if (!expiring) {
            setLastAct();
        }
    };

    if (getLastAct() <= 0) {
        setLastAct();
    }

    checkLocal();

    setInterval(() => {
        if (isExpired()) {
            doLogout();
            return;
        }

        checkServer();
    }, 5 * 60 * 1000);

    setInterval(checkLocal, 30 * 1000);

    ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, markActivity, { passive: true });
    });

    const checkAfterResume = () => {
        checkLocal();
        checkServer();
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            checkAfterResume();
        }
    });

    window.addEventListener('focus', checkAfterResume);
    window.addEventListener('pageshow', checkAfterResume);
})();
</script>
