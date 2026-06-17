<script>
    (() => {
        const form = document.getElementById('login-form');
        const tokenKey = 'coe_tab_auth_token';
        const userKey = 'coe_tab_auth_user_id';
        const backRefreshKey = 'coe_login_back_refresh_done';
        const adminActivityKey = 'admin_last_activity_at';
        const adminInitializedKey = 'admin_session_timeout_initialized';
        const anggotaActivityKey = 'anggota_last_activity_at';
        const anggotaInitializedKey = 'anggota_session_timeout_initialized';
        const rememberUsernameKey = 'coe_remember_username';
        const rememberCheckedKey = 'coe_remember_checked';
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const rememberInput = document.getElementById('remember_me');
        const submitButton = form?.querySelector('button[type="submit"]');
        const errorBox = document.getElementById('login-error-box');
        const errorList = document.getElementById('login-error-list');
        const isTabReauth = window.location.search.includes('tab_reauth=1');

        if (isTabReauth) {
            sessionStorage.removeItem(tokenKey);
            sessionStorage.removeItem(userKey);
        }

        const navigationEntry = performance.getEntriesByType('navigation')[0];
        const isBackForwardNavigation = navigationEntry?.type === 'back_forward';

        window.addEventListener('pageshow', (event) => {
            const needsRefresh = event.persisted || isBackForwardNavigation;

            if (!needsRefresh || sessionStorage.getItem(backRefreshKey) === '1') {
                sessionStorage.removeItem(backRefreshKey);
                return;
            }

            sessionStorage.setItem(backRefreshKey, '1');
            window.location.reload();
        });

        if (isTabReauth || window.location.search.includes('next=')) {
            window.history.replaceState({}, document.title, '{{ route('login') }}');
        }

        if (usernameInput && rememberInput && localStorage.getItem(rememberCheckedKey) === '1') {
            usernameInput.value = localStorage.getItem(rememberUsernameKey) || usernameInput.value || '';
            rememberInput.checked = true;
        }

        if (!form) {
            return;
        }

        const createTabToken = (userId = null) => {
            sessionStorage.removeItem(backRefreshKey);
            sessionStorage.removeItem(adminInitializedKey);
            sessionStorage.removeItem(anggotaInitializedKey);
            localStorage.setItem(adminActivityKey, String(Date.now()));
            localStorage.setItem(anggotaActivityKey, String(Date.now()));

            const token = (window.crypto && window.crypto.randomUUID)
                ? window.crypto.randomUUID()
                : `${Date.now()}-${Math.random()}`;

            sessionStorage.setItem(tokenKey, token);

            if (userId !== null && userId !== undefined && userId !== '') {
                sessionStorage.setItem(userKey, String(userId));
            } else {
                sessionStorage.removeItem(userKey);
            }

            if (rememberInput?.checked) {
                localStorage.setItem(rememberCheckedKey, '1');
                localStorage.setItem(rememberUsernameKey, usernameInput?.value || '');
            } else {
                localStorage.removeItem(rememberCheckedKey);
                localStorage.removeItem(rememberUsernameKey);
            }
        };

        const renderErrors = (messages) => {
            if (!errorBox || !errorList) {
                return;
            }

            errorList.innerHTML = '';

            messages.forEach((message) => {
                const item = document.createElement('li');
                item.textContent = message;
                errorList.appendChild(item);
            });

            errorBox.classList.remove('hidden');
            errorBox.classList.add('flex');
        };

        const getClientErrors = () => {
            const messages = [];

            if (!usernameInput?.value.trim()) {
                messages.push('Username wajib diisi.');
            }

            if (!passwordInput?.value) {
                messages.push('Password wajib diisi.');
            }

            return messages;
        };

        form.addEventListener('submit', async (event) => {
            if (!window.fetch || !window.FormData) {
                createTabToken();
                return;
            }

            event.preventDefault();

            if (errorBox) {
                errorBox.classList.add('hidden');
                errorBox.classList.remove('flex');
            }

            const clientErrors = getClientErrors();

            if (clientErrors.length) {
                renderErrors(clientErrors);
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-70', 'cursor-not-allowed');
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    sessionStorage.removeItem(tokenKey);
                    sessionStorage.removeItem(userKey);
                    sessionStorage.removeItem(backRefreshKey);

                    const messages = Object.values(data.errors || {})
                        .flat()
                        .filter(Boolean);

                    renderErrors(messages.length ? messages : [data.message || 'Login gagal.']);
                    return;
                }

                createTabToken(data.user_id);

                if (data.redirect) {
                    window.location.replace(data.redirect);
                    return;
                }

                window.location.replace('{{ url('/') }}');
            } catch (error) {
                sessionStorage.removeItem(tokenKey);
                sessionStorage.removeItem(userKey);
                sessionStorage.removeItem(backRefreshKey);
                renderErrors(['Terjadi kendala saat login. Silakan coba lagi.']);
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
                }
            }
        });
    })();
</script>
