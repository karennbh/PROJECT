@php
    $fallbackUrl = $fallbackUrl ?? null;
@endphp

@if ($fallbackUrl)
    <script>
        (() => {
            const fallbackUrl = @js($fallbackUrl);
            const currentUrl = window.location.href;
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const fallbackPath = new URL(fallbackUrl, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            const stateKey = `feature-back-guard:${currentUrl}`;
            const segments = currentPath.split('/').filter(Boolean);

            const shouldGuard = (() => {
                if (currentPath === fallbackPath) {
                    return false;
                }

                if (currentPath.startsWith('/admin')) {
                    // Hanya aktif di halaman list resource admin: /admin/nama-resource
                    return segments.length === 2;
                }

                // Untuk area anggota, hanya aktif di halaman fitur level utama: /peminjaman, /pemakaian, dst.
                return segments.length === 1;
            })();

            if (!shouldGuard) {
                return;
            }

            if (!window.history.state || window.history.state.__featureBackGuard !== stateKey) {
                window.history.pushState({
                    __featureBackGuard: stateKey,
                    __featureBackFallback: fallbackUrl,
                }, '', currentUrl);
            }

            window.addEventListener('popstate', (event) => {
                const targetUrl = event.state?.__featureBackFallback ?? fallbackUrl;

                if (!targetUrl || window.location.href === targetUrl) {
                    return;
                }

                // Gunakan assign agar urutan history tetap natural:
                // fitur detail -> list -> dashboard, dan tombol forward tetap bisa kembali.
                window.location.assign(targetUrl);
            });
        })();
    </script>
@endif
