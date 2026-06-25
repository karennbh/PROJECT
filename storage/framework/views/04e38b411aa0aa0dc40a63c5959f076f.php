<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aset & BHP</title>
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
                        navy: {
                            900: '#021024',
                            800: '#052659',
                            700: '#023e8a',
                        },
                        ocean: {
                            500: '#0077b6',
                            400: '#0096c7',
                            300: '#00b4d8',
                            200: '#48cae4',
                            100: '#90e0ef',
                            50: '#caf0f8',
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
        /* Animasi halus untuk background */
        .blob {
            position: absolute;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 10s infinite ease-in-out;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -20px) scale(1.1); }
            100% { transform: translate(0, 0) scale(1); }
        }
        .input-group:focus-within label {
            color: #023e8a;
        }
        .input-group:focus-within svg {
            color: #023e8a;
        }
        .login-shell {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(125, 211, 252, 0.34), transparent 26%),
                radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.22), transparent 30%),
                linear-gradient(145deg, #e0f2fe 0%, #f0f9ff 38%, #dbeafe 100%);
        }
        .login-shell::before {
            content: '';
            position: absolute;
            inset: 18px;
            border-radius: 28px;
            background:
                linear-gradient(145deg, rgba(255,255,255,0.78), rgba(224,242,254,0.72));
            border: 1px solid rgba(125, 211, 252, 0.55);
            box-shadow:
                0 20px 50px rgba(2, 36, 73, 0.09),
                inset 0 1px 0 rgba(255,255,255,0.9);
        }
        .login-shell > * {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 selection:bg-ocean-100 selection:text-navy-900">

    <div class="min-h-screen flex w-full">
        
        <div class="hidden lg:flex w-1/2 bg-navy-900 relative overflow-hidden flex-col justify-between p-12 text-white z-10">
            
            <div class="blob bg-ocean-500 w-96 h-96 rounded-full -top-20 -left-20 mix-blend-screen"></div>
            <div class="blob bg-navy-700 w-80 h-80 rounded-full bottom-0 right-0 animation-delay-2000"></div>
            
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-20"></div>

            <div class="relative z-10">
                <?php echo $__env->make('partials.app-logo', ['theme' => 'dark'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="relative z-10 max-w-lg mb-20">
                <h1 class="text-5xl font-extrabold leading-tight mb-6">
                    Kelola Aset & <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-ocean-200 to-white">Barang Habis Pakai</span>
                </h1>
                <p class="text-ocean-100 text-lg font-light leading-relaxed">
                    Aplikasi Pengelolaan Aset Tetap dan Barang Habis Pakai.
                </p>
            </div>

            <div class="relative z-10 text-sm text-ocean-200/50">
                &copy; <?php echo e(date('Y')); ?> CoE Smart EV. Karen Natalia Naibaho
            </div>
        </div>

        <div class="login-shell w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12 relative">
            
            <div class="w-full max-w-[420px]">
                <div class="lg:hidden flex justify-center mb-8">
                    <?php echo $__env->make('partials.app-logo', ['theme' => 'light', 'compact' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-navy-900 mb-2">Selamat Datang</h2>
                    <p class="text-slate-500 text-sm">Silakan masukkan Akun Anda untuk masuk.</p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div id="login-error-box" class="mb-6 bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-xl text-sm flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="min-w-0">
                            <span class="font-bold block mb-1">Login Gagal</span>
                            <ul id="login-error-list" class="list-disc pl-4 space-y-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div id="login-error-box" class="mb-6 bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-xl text-sm items-start gap-3 hidden">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="min-w-0">
                            <span class="font-bold block mb-1">Login Gagal</span>
                            <ul id="login-error-list" class="list-disc pl-4 space-y-1"></ul>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl text-sm font-medium">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div class="mb-6 bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-xl text-sm font-medium">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form id="login-form" action="<?php echo e(route('login.post')); ?>" method="POST" class="space-y-6" novalidate>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="next" value="<?php echo e(request('next')); ?>">
                    
                    <div class="input-group">
                        <label for="username" class="block text-xs font-bold text-navy-800 uppercase tracking-wider mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <input type="text" name="username" id="username" value="<?php echo e(old('username')); ?>"
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-navy-900 text-sm font-medium placeholder-slate-400 focus:outline-none focus:border-ocean-300 focus:ring-4 focus:ring-ocean-50 transition-all shadow-sm"
                                placeholder="Masukkan username">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-bold text-navy-800 uppercase tracking-wider">Password</label>
                            
                            
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" id="password"
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-navy-900 text-sm font-medium placeholder-slate-400 focus:outline-none focus:border-ocean-300 focus:ring-4 focus:ring-ocean-50 transition-all shadow-sm"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" 
                            class="h-4 w-4 text-ocean-500 focus:ring-ocean-400 border-gray-300 rounded cursor-pointer">
                        <label for="remember_me" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-ocean-500/30 text-sm font-bold text-white bg-gradient-to-r from-navy-800 to-ocean-500 hover:to-ocean-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all transform hover:scale-[1.02] active:scale-95">
                        Masuk Dashboard
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-slate-400 text-xs">
                        Jika Anda mengalami kendala saat login, <br>silakan hubungi <a href="#" class="text-ocean-600 font-bold hover:underline">Administrator</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php echo $__env->make('partials.tab-auth-login-script', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH /var/www/project/resources/views/auth/login.blade.php ENDPATH**/ ?>