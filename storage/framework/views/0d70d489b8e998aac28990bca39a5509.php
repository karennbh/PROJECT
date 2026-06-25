<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', 'Pengelolaan Aset & BHP'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/logo.png')); ?>?v=2">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('assets/logo.png')); ?>?v=2">
    <link rel="apple-touch-icon" href="<?php echo e(asset('assets/logo.png')); ?>?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#021024',   /* Navy Paling Gelap */
                            navy: '#052659',   /* Navy Medium */
                            blue: '#005b96',   /* Biru Standar */
                            cyan: '#48cae4',   /* Cyan Terang */
                            light: '#caf0f8',  /* Warna Highlight dari Gambar Anda */
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f9ff; overflow-x: hidden; }

        .anggota-ui {
            font-size: 16px;
            color: #334155;
        }

        .anggota-ui h2 {
            line-height: 1.15;
        }

        .anggota-ui .page-section-title {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.025em;
            color: #1e293b;
        }

        .anggota-ui .page-section-subtitle {
            margin-top: 0.45rem;
            font-size: 1rem;
            line-height: 1.65;
            color: #64748b;
        }

        .anggota-ui .panel-title {
            font-size: 1.125rem;
            font-weight: 800;
            line-height: 1.35;
            color: #1e293b;
        }

        .anggota-ui .panel-subtitle {
            margin-top: 0.35rem;
            font-size: 0.95rem;
            line-height: 1.55;
            color: #64748b;
        }

        .anggota-ui .modern-label {
            font-size: 0.82rem !important;
            letter-spacing: 0.06em !important;
        }

        .anggota-ui .modern-input,
        .anggota-ui .modern-textarea,
        .anggota-ui select,
        .anggota-ui input[type="date"],
        .anggota-ui input[type="text"],
        .anggota-ui input[type="number"],
        .anggota-ui input[type="url"],
        .anggota-ui input[type="file"],
        .anggota-ui textarea {
            font-size: 0.98rem !important;
            line-height: 1.5 !important;
        }

        .anggota-ui table th {
            font-size: 0.88rem !important;
            line-height: 1.4 !important;
        }

        .anggota-ui table td {
            font-size: 0.97rem;
            line-height: 1.6;
        }

        .anggota-ui .status-badge {
            font-size: 0.78rem !important;
            padding: 0.4rem 0.85rem !important;
            line-height: 1.2;
        }

        .anggota-ui .pagination,
        .anggota-ui nav[role="navigation"] {
            font-size: 0.95rem;
        }

        /* --- SIDEBAR STYLING --- */
        #sidebar {
            background: linear-gradient(180deg, #021024 0%, #052659 100%); /* Gradasi Navy */
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 260px; 
            border-right: 1px solid rgba(255,255,255,0.05);
        }
        
        .sidebar-collapsed #sidebar { width: 80px; } 
        .sidebar-collapsed .menu-text, .sidebar-collapsed .sidebar-desc, .sidebar-collapsed .main-menu-label, .sidebar-collapsed .logout-label { display: none; opacity: 0; }
        .sidebar-collapsed .timeline-line { opacity: 0; }
        .sidebar-collapsed .nav-item { justify-content: center; padding: 12px 0; margin: 8px 12px; }
        
        /* Nav Items */
        .nav-item { 
            position: relative; 
            z-index: 10; 
            transition: all 0.3s ease; 
            color: #a9ebf7;
            margin: 8px 16px; 
            border-radius: 18px; 
            display: flex; 
            align-items: center; 
            border: 1px solid transparent;
            padding: 14px 16px;
            backdrop-filter: blur(10px);
        }
        
        .nav-item:hover { 
            color: #ffffff; 
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(144, 224, 239, 0.12);
            transform: translateX(4px);
        }
        
        /* ACTIVE STATE - Menggunakan Warna #CAF0F8 */
        .nav-item.active { 
            color: #021024; /* Text Navy Gelap */
            background: linear-gradient(135deg, #caf0f8 0%, #b8ecfb 100%);
            font-weight: 800;
            border-color: rgba(255,255,255,0.35);
            box-shadow: 0 14px 30px rgba(72, 202, 228, 0.28), inset 0 1px 0 rgba(255,255,255,0.55);
        }
        
        .nav-dot { 
            width: 8px;
            height: 8px;
            min-width: 8px;
            min-height: 8px;
            background: #48cae4; 
            border-radius: 50%; 
            transition: all 0.3s ease; 
            box-shadow: 0 0 0 2px #052659; 
        }
        
        .nav-item.active .nav-dot { 
            background: #021024; 
            box-shadow: 0 0 0 2px #caf0f8; 
            transform: scale(1); 
        }

        .menu-title {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.015em;
            line-height: 1.35;
        }

        .nav-item.active .menu-title {
            font-weight: 800;
        }

        .menu-desc {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.015em;
            line-height: 1.45;
            margin-top: 0.35rem;
            opacity: 0.82;
        }

        /* Timeline Line */
        .timeline-line { position: absolute; left: 36px; top: 20px; bottom: 20px; width: 1px; background: linear-gradient(to bottom, transparent, rgba(72, 202, 228, 0.3), transparent); z-index: 1; }
        
        /* Topbar & Content */
        .main-content { transition: all 0.4s ease; margin-left: 260px; }
        .sidebar-collapsed .main-content { margin-left: 80px; }
        #topbar { transition: all 0.4s ease; left: 260px; height: 70px; }
        .sidebar-collapsed #topbar { left: 80px; }

        #sidebar-overlay {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        #sidebar-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }
        
        /* Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        /* Profile Panel Header */
        .profile-header-curved {
            background: linear-gradient(135deg, #021024 0%, #005b96 100%);
            height: 80px;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            position: relative;
        }

        /* Utility animations */
        #profile-overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        #profile-overlay.open { opacity: 1; pointer-events: auto; }
        #profile-panel { transform: translateX(100%); transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1); }
        #profile-overlay.open #profile-panel { transform: translateX(0); }

        @media (max-width: 1024px) {
            #sidebar {
                width: 280px;
                transform: translateX(-100%);
                z-index: 70;
            }

            .sidebar-collapsed #sidebar {
                width: 280px;
            }

            #layout-root.sidebar-mobile-open #sidebar {
                transform: translateX(0);
            }

            .main-content,
            .sidebar-collapsed .main-content {
                margin-left: 0;
            }

            #topbar,
            .sidebar-collapsed #topbar {
                left: 0;
                padding-left: 1rem;
                padding-right: 1rem;
            }

            main {
                padding: 1rem;
            }

            .nav-item {
                margin-left: 12px;
                margin-right: 12px;
            }
        }

        @media (max-width: 768px) {
            #topbar {
                height: auto;
                min-height: 70px;
            }

            .main-content {
                min-width: 0;
            }
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div id="layout-root" class="min-h-screen flex">
        
        <aside id="sidebar" class="fixed top-0 left-0 h-screen flex flex-col z-50 shadow-2xl overflow-hidden">
            <div class="h-[80px] flex items-center gap-3 px-6 pt-2 flex-shrink-0">
                <?php echo $__env->make('partials.app-logo', ['theme' => 'dark'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="flex-1 overflow-y-auto nav-container custom-scrollbar relative">
                <div class="pt-4 pb-4"> 
                    <div class="timeline-line"></div>
                    <div class="px-8 text-xs uppercase tracking-[0.2em] text-brand-cyan/50 font-bold mb-4 main-menu-label pl-12">Navigasi Utama</div>
                    
                    <nav class="relative z-10 space-y-1.5">
                        <?php
                            $menus = [
                                ['url' => '/dashboard', 'label' => 'Dashboard', 'desc' => 'Ringkasan & Statistik'],
                                ['url' => '/ketersediaan', 'label' => 'Mencari Ketersediaan Barang', 'desc' => 'Stok Barang Real-time'],
                                ['url' => '/peminjaman', 'label' => 'Peminjaman Barang Kantor', 'desc' => 'Kelola Peminjaman Barang Kantor'],
                                ['url' => '/pemakaian', 'label' => 'Pemakaian Barang Habis Pakai', 'desc' => 'Kelola Pemakaian BHP'],
                                ['url' => '/pembelian', 'label' => 'Pembelian Barang Kantor', 'desc' => 'Kelola Pengadaan Barang'],
                            ];
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $isActive = request()->is(ltrim($menu['url'], '/').'*'); ?>
                            <a href="<?php echo e($menu['url']); ?>" class="nav-item group gap-4 <?php echo e($isActive ? 'active' : ''); ?>">
                                <div class="nav-dot"></div>
                                <div class="menu-text flex flex-col">
                                    <span class="menu-title"><?php echo e($menu['label']); ?></span>
                                    <span class="menu-desc <?php echo e($isActive ? 'text-brand-dark/70' : 'text-brand-light/40'); ?> hidden group-hover:block transition-all"><?php echo e($menu['desc']); ?></span>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </nav>
                </div>
            </div>

            <div class="p-4 border-t border-white/5 bg-black/20 backdrop-blur-md">
                <form action="<?php echo e(route('logout')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full logout-btn flex items-center gap-3 py-3 px-4 rounded-xl transition-all duration-300 hover:bg-white/10 text-brand-cyan/70 hover:text-white group">
                        <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="logout-label text-base font-bold tracking-wide">Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 bg-brand-dark/35 backdrop-blur-[2px] z-[65]" onclick="closeSidebarOnMobile()"></div>

        <div class="main-content flex-1 flex flex-col min-h-screen relative z-0">
            <header id="topbar" class="fixed top-0 right-0 h-[70px] bg-white/80 backdrop-blur-md shadow-[0_4px_20px_-10px_rgba(0,0,0,0.05)] border-b border-brand-light z-40 flex items-center justify-between px-8">
                <div class="flex items-center gap-4">
                    <button id="sidebar-toggle" class="p-2 rounded-lg text-slate-400 hover:bg-brand-light hover:text-brand-navy transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    </button>
                    <div class="h-8 w-px bg-slate-200 mx-2"></div>
                    <h1 class="text-base font-bold text-brand-navy tracking-tight"><?php echo $__env->yieldContent('page_title', 'Overview Dashboard'); ?></h1>
                </div>
                
                <button onclick="toggleProfilePanel()" class="flex items-center gap-3 group p-1.5 pr-4 rounded-full hover:bg-brand-light/50 transition-all border border-transparent hover:border-brand-light">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-brand-navy group-hover:text-brand-blue transition-colors"><?php echo e(Auth::user()->name ?? 'Administrator'); ?></p>
                        <p class="text-xs font-medium text-slate-400"><?php echo e(Auth::user()->user_group ?? 'Super Admin'); ?></p>
                    </div>
                    <div class="relative">
                        <div class="h-9 w-9 rounded-full bg-gradient-to-br from-brand-dark to-brand-navy flex items-center justify-center text-white font-bold shadow-md ring-2 ring-white group-hover:ring-brand-light transition-all">
                            <?php echo e(substr(Auth::user()->name ?? 'A', 0, 1)); ?>

                        </div>
                        <div class="absolute bottom-0 right-0 h-2.5 w-2.5 bg-emerald-500 border-2 border-white rounded-full"></div>
                    </div>
                </button>
            </header>

            <main class="anggota-ui mt-[70px] p-6 lg:p-8">
                <?php echo $__env->yieldContent('content'); ?>
            </main>
        </div>
    </div>

    <div id="profile-overlay" class="fixed inset-0 z-[60] flex justify-end">
        <div onclick="toggleProfilePanel()" class="absolute inset-0 bg-brand-dark/20 backdrop-blur-sm transition-opacity"></div>
        <div id="profile-panel" class="relative w-full max-w-[320px] bg-white h-full shadow-2xl flex flex-col border-l border-slate-100">
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="profile-header-curved flex-shrink-0 flex items-start justify-between px-6 pt-6">
                    <h3 class="text-white font-bold text-lg">Profil Saya</h3>
                    <button onclick="toggleProfilePanel()" class="text-white/70 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <div class="px-6 relative z-10">
                    <div class="relative -mt-10 mb-6 flex flex-col items-center">
                        <div class="h-20 w-20 rounded-full bg-white p-1 shadow-xl">
                            <div class="h-full w-full rounded-full bg-brand-navy flex items-center justify-center text-white text-2xl font-bold">
                                <?php echo e(substr(Auth::user()->name ?? 'A', 0, 1)); ?>

                            </div>
                        </div>
                        <h2 class="mt-3 text-lg font-bold text-brand-dark"><?php echo e(Auth::user()->name ?? 'Administrator'); ?></h2>
                        <span class="text-xs text-slate-500"><?php echo e(Auth::user()->username ?? Auth::user()->name ?? 'admin'); ?></span>
                    </div>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-8">
                        <?php echo csrf_field(); ?>
                        <button class="w-full py-2.5 bg-rose-50 text-rose-600 font-bold text-xs rounded-lg hover:bg-rose-100 transition-colors">Keluar Aplikasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php echo $__env->make('partials.tab-auth-guard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('partials.anggota-session-timeout', [
        'timeoutMs' => (int) config('session.lifetime', 45) * 60 * 1000,
        'logoutUrl' => route('logout'),
        'loginUrl' => route('login'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->renderWhen(!request()->routeIs('anggota.dashboard'), 'partials.feature-back-guard', ['fallbackUrl' => route('anggota.dashboard')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1])); ?>

    <script>
        function toggleProfilePanel() {
            const overlay = document.getElementById('profile-overlay');
            overlay.classList.toggle('open');
        }

        function isMobileSidebar() {
            return window.innerWidth <= 1024;
        }

        function closeSidebarOnMobile() {
            const root = document.getElementById('layout-root');
            const overlay = document.getElementById('sidebar-overlay');

            if (!isMobileSidebar()) {
                return;
            }

            root.classList.remove('sidebar-mobile-open');
            overlay.classList.remove('open');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const root = document.getElementById('layout-root');
            const toggleBtn = document.getElementById('sidebar-toggle');
            const overlay = document.getElementById('sidebar-overlay');

            toggleBtn.addEventListener('click', function () {
                if (isMobileSidebar()) {
                    root.classList.toggle('sidebar-mobile-open');
                    overlay.classList.toggle('open', root.classList.contains('sidebar-mobile-open'));
                    return;
                }

                root.classList.toggle('sidebar-collapsed');
            });

            window.addEventListener('resize', function () {
                if (!isMobileSidebar()) {
                    root.classList.remove('sidebar-mobile-open');
                    overlay.classList.remove('open');
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH /var/www/project/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>