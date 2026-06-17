<script>
    (() => {
        if (window.__adminUnsavedNavigationGuardInstalled) {
            window.__adminUnsavedNavigationGuardRefresh?.();

            return;
        }

        window.__adminUnsavedNavigationGuardInstalled = true;

        let guardAbort = null;
        let guardKey = null;
        let initialSnapshot = '';
        let isDirty = false;
        let isSubmitting = false;
        let trackingReady = false;

        const isAdminFormPage = () => {
            const path = window.location.pathname.replace(/\/+$/, '');

            return /^\/admin\/.+\/create$/.test(path)
                || /^\/admin\/.+\/[^/]+\/edit$/.test(path)
                || document.querySelector('[wire\\:submit], form') !== null && /\/admin\/.+\/(create|edit)(\/|$)/.test(path);
        };

        const ensureDialog = () => {
            document.getElementById('admin-navigation-guard-style')?.remove();
            document.getElementById('admin-navigation-guard-dialog')?.remove();

            const dialog = document.createElement('div');
            dialog.id = 'admin-navigation-guard-dialog';
            dialog.hidden = true;
            dialog.innerHTML = `
                <div class="admin-navigation-guard__backdrop" data-guard-cancel></div>
                <div class="admin-navigation-guard__panel" role="dialog" aria-modal="true" aria-labelledby="admin-navigation-guard-title">
                    <h2 id="admin-navigation-guard-title">Data belum disimpan</h2>
                    <p>Jika keluar sekarang, data yang sudah diisi pada form ini tidak akan tersimpan.</p>
                    <div class="admin-navigation-guard__actions">
                        <button type="button" class="admin-navigation-guard__button admin-navigation-guard__button--secondary" data-guard-cancel>Batal</button>
                        <button type="button" class="admin-navigation-guard__button admin-navigation-guard__button--primary" data-guard-confirm>Keluar / Lanjutkan</button>
                    </div>
                </div>
            `;
            dialog.className = 'admin-navigation-guard';

            const style = document.createElement('style');
            style.id = 'admin-navigation-guard-style';
            style.textContent = `
                .admin-navigation-guard[hidden] { display: none; }
                .admin-navigation-guard { position: fixed; inset: 0; z-index: 99999; display: grid; place-items: center; padding: 1rem; }
                .admin-navigation-guard__backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .48); backdrop-filter: blur(2px); }
                .admin-navigation-guard__panel { position: relative; width: min(28rem, 100%); border-radius: 14px; background: #fff; padding: 1.25rem; box-shadow: 0 24px 80px rgba(15, 23, 42, .25); color: #0f172a; }
                .admin-navigation-guard__panel h2 { margin: 0; font-size: 1.125rem; font-weight: 700; }
                .admin-navigation-guard__panel p { margin: .65rem 0 0; color: #475569; line-height: 1.55; }
                .admin-navigation-guard__actions { display: flex; justify-content: flex-end; gap: .75rem; margin-top: 1.25rem; }
                .admin-navigation-guard__button { border: 0; border-radius: .65rem; padding: .6rem 1rem; font-weight: 700; cursor: pointer; }
                .admin-navigation-guard__button--secondary { background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0; }
                .admin-navigation-guard__button--primary { background: #06b6d4; color: #fff; }
            `;

            document.head.appendChild(style);
            document.body.appendChild(dialog);

            return dialog;
        };

        const snapshotForms = () => {
            const fields = Array.from(document.querySelectorAll('form input, form textarea, form select'))
                .filter((field) => {
                    if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                        return false;
                    }

                    if (field.type === 'hidden' || field.type === 'button' || field.type === 'submit') {
                        return false;
                    }

                    return ! field.disabled;
                })
                .map((field) => {
                    const key = field.name || field.id || field.getAttribute('wire:model') || field.getAttribute('x-model') || '';

                    if (field instanceof HTMLInputElement && (field.type === 'checkbox' || field.type === 'radio')) {
                        return [key, field.checked ? '1' : '0'];
                    }

                    if (field instanceof HTMLInputElement && field.type === 'file') {
                        return [key, Array.from(field.files ?? []).map((file) => `${file.name}:${file.size}`).join('|')];
                    }

                    return [key, field.value ?? ''];
                });

            return JSON.stringify(fields);
        };

        const hasFormChanged = () => {
            if (! trackingReady) {
                return false;
            }

            if (isDirty) {
                return true;
            }

            return initialSnapshot !== '' && snapshotForms() !== initialSnapshot;
        };

        const askToLeave = () => new Promise((resolve) => {
            const dialog = document.getElementById('admin-navigation-guard-dialog') ?? ensureDialog();
            dialog.hidden = false;

            const close = (answer) => {
                dialog.hidden = true;
                dialog.querySelectorAll('[data-guard-confirm], [data-guard-cancel]').forEach((button) => {
                    button.removeEventListener('click', onConfirm);
                    button.removeEventListener('click', onCancel);
                });
                resolve(answer);
            };

            const onConfirm = () => close(true);
            const onCancel = () => close(false);

            dialog.querySelector('[data-guard-confirm]')?.addEventListener('click', onConfirm);
            dialog.querySelectorAll('[data-guard-cancel]').forEach((button) => {
                button.addEventListener('click', onCancel);
            });
        });

        const shouldHandleUrl = (href) => {
            if (! href || href.startsWith('#')) {
                return false;
            }

            const url = new URL(href, window.location.href);

            if (! ['http:', 'https:'].includes(url.protocol)) {
                return false;
            }

            if (url.origin !== window.location.origin) {
                return false;
            }

            return url.href !== window.location.href;
        };

        const leaveWithReplace = (href) => {
            isSubmitting = true;
            isDirty = false;
            window.location.replace(href);
        };

        const markSubmitting = () => {
            const wasDirty = isDirty;

            isSubmitting = true;
            isDirty = false;

            setTimeout(() => {
                if (document.visibilityState === 'visible') {
                    isSubmitting = false;
                    isDirty = wasDirty;
                }
            }, 3000);
        };

        const startGuard = () => {
            const nextGuardKey = `admin-unsaved-navigation-guard:${window.location.href}`;

            if (! isAdminFormPage()) {
                guardAbort?.abort();
                guardAbort = null;
                guardKey = null;
                isDirty = false;
                isSubmitting = false;
                trackingReady = false;

                return;
            }

            if (guardKey === nextGuardKey) {
                return;
            }

            guardAbort?.abort();
            guardAbort = new AbortController();
            guardKey = nextGuardKey;
            isDirty = false;
            isSubmitting = false;
            trackingReady = false;

            ensureDialog();

            const listenerOptions = {
                capture: true,
                signal: guardAbort.signal,
            };

            setTimeout(() => {
                initialSnapshot = snapshotForms();
                trackingReady = true;
            }, 800);

            document.addEventListener('input', (event) => {
                if (trackingReady && event.target instanceof Element && event.target.closest('form')) {
                    isDirty = true;
                }
            }, listenerOptions);

            document.addEventListener('change', (event) => {
                if (trackingReady && event.target instanceof Element && event.target.closest('form')) {
                    isDirty = true;
                }
            }, listenerOptions);

            document.addEventListener('submit', () => {
                markSubmitting();
            }, listenerOptions);

            document.addEventListener('click', async (event) => {
                const submitButton = event.target instanceof Element
                    ? event.target.closest('button[type="submit"], input[type="submit"]')
                    : null;

                if (submitButton && submitButton.closest('form')) {
                    markSubmitting();

                    return;
                }

                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

                if (! link || link.target === '_blank' || link.hasAttribute('download')) {
                    return;
                }

                if (! shouldHandleUrl(link.href)) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                if (hasFormChanged() && ! isSubmitting) {
                    const shouldLeave = await askToLeave();

                    if (! shouldLeave) {
                        return;
                    }
                }

                leaveWithReplace(link.href);
            }, listenerOptions);
        };

        window.__adminUnsavedNavigationGuardRefresh = startGuard;

        startGuard();
        document.addEventListener('DOMContentLoaded', startGuard);
        document.addEventListener('livewire:navigated', startGuard);
        document.addEventListener('livewire:navigating', startGuard);
        document.addEventListener('turbo:load', startGuard);
    })();
</script>
<?php /**PATH C:\xampp\htdocs\TA2025\resources\views/filament/admin/hooks/navigation-guard.blade.php ENDPATH**/ ?>