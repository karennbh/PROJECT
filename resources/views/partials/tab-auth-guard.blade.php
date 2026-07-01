<script>
    (() => {
        const tokenKey = 'coe_tab_auth_token';
        const userKey = 'coe_tab_auth_user_id';
        const backRefreshKey = 'coe_auth_back_refresh_done';
        const token = sessionStorage.getItem(tokenKey);
        const serverRenderedUserId = @json(auth()->id());
        let expectedUserId = sessionStorage.getItem(userKey);
        const currentPath = `${window.location.pathname}${window.location.search}${window.location.hash}`;
        const loginUrl = @json(route('login', ['tab_reauth' => 1])) + '&next=' + encodeURIComponent(currentPath);
        const sessionUserUrl = @json(route('auth.session-user'));
        const navigationEntry = performance.getEntriesByType('navigation')[0];
        const isBackForwardNavigation = navigationEntry?.type === 'back_forward';
        const isAdminFormPage = /^\/admin\/.+\/(create|[^/]+\/edit)$/.test(window.location.pathname);

        const redirectToLogin = () => {
            sessionStorage.removeItem(tokenKey);
            sessionStorage.removeItem(userKey);
            window.location.replace(loginUrl);
        };

        if (!expectedUserId && serverRenderedUserId) {
            expectedUserId = String(serverRenderedUserId);
            sessionStorage.setItem(userKey, expectedUserId);
        }

        const validateCurrentSessionUser = async () => {
            if (!expectedUserId) {
                return;
            }

            try {
                const response = await fetch(sessionUserUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) {
                    redirectToLogin();
                    return;
                }

                const data = await response.json();

                if (!data.authenticated || String(data.user_id) !== String(expectedUserId)) {
                    redirectToLogin();
                }
            } catch (error) {
                // Biarkan user tetap di halaman saat koneksi sesaat bermasalah.
            }
        };

        window.addEventListener('pageshow', (event) => {
            if (isAdminFormPage) {
                return;
            }

            const needsRefresh = event.persisted || isBackForwardNavigation;

            if (!needsRefresh) {
                sessionStorage.removeItem(backRefreshKey);
                return;
            }

            if (sessionStorage.getItem(backRefreshKey) === '1') {
                sessionStorage.removeItem(backRefreshKey);
                validateCurrentSessionUser();
                return;
            }

            sessionStorage.setItem(backRefreshKey, '1');
            window.location.reload();
        });

        if (!token) {
            redirectToLogin();
            return;
        }

        window.addEventListener('focus', validateCurrentSessionUser);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                validateCurrentSessionUser();
            }
        });
        window.setInterval(validateCurrentSessionUser, 60000);

        if (!('BroadcastChannel' in window)) {
            return;
        }

        const instanceId = (window.crypto && window.crypto.randomUUID)
            ? window.crypto.randomUUID()
            : `${Date.now()}-${Math.random()}`;

        const channel = new BroadcastChannel('coe-tab-auth-guard');
        let redirected = false;

        channel.onmessage = (event) => {
            const data = event.data || {};

            if (data.type === 'probe' && data.token === token && data.instanceId !== instanceId) {
                channel.postMessage({
                    type: 'active',
                    token,
                    target: data.instanceId,
                });
            }

            if (data.type === 'active' && data.token === token && data.target === instanceId && !redirected) {
                redirected = true;
                redirectToLogin();
            }
        };

        window.addEventListener('beforeunload', () => {
            channel.close();
        });

        setTimeout(() => {
            channel.postMessage({
                type: 'probe',
                token,
                instanceId,
            });
        }, 60);
    })();
</script>
