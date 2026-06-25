<?php $__env->startSection('title', 'Dashboard Overview'); ?>
<?php $__env->startSection('page_title', 'Dashboard Pengajuan'); ?>

<?php
    // 1. STATISTIK HEADER
    $totalAsetCount = \App\Models\BarangKantor::where('kategori_barang', 'aset')->count();
    $jumlahUnitAset = (int) \App\Models\BarangKantor::where('kategori_barang', 'aset')->sum('stok') ?: $totalAsetCount;
    $asetAktif = \App\Models\BarangKantor::where('kategori_barang', 'aset')->where('status_barang', 'Aktif')->count();
    $totalBHPCount = \App\Models\BarangKantor::where('kategori_barang', 'bhp')->count();
    $jumlahStokBHP = \App\Models\BarangKantor::where('kategori_barang', 'bhp')->sum('stok');
    $bhpMenipis = \App\Models\BarangKantor::query()->bhpStokMenipis()->count();
    $bhpHabis = \App\Models\BarangKantor::query()->bhpStokHabis()->count();
    $bhpMenipisHabis = $bhpMenipis + $bhpHabis;
    
    // Hitung Pending dari semua modul
    $pendingPeminjaman = \App\Models\PeminjamanBarang::where('status_pinjam', 'pending')->count();
    $pendingPemakaian = \App\Models\PemakaianBHP::where('status', 'pending')->count();
    $pendingPembelian = \App\Models\PengajuanPembelianBarang::where('status', 'pending')->count();
    $totalPending = $pendingPeminjaman + $pendingPemakaian + $pendingPembelian;

    // 2. KETERSEDIAAN BARANG (Ambil 3 data terbaru/stok terendah)
    $ketersediaanBarang = \App\Models\BarangKantor::orderBy('stok', 'asc')->take(3)->get();

    $userId = auth()->id();

    // 1. Log Peminjaman (Filter data milik user login saja)
    $logPeminjaman = \App\Models\PeminjamanBarang::with(['user', 'barang'])
        ->where('user_id', $userId) 
        ->latest()
        ->take(2)
        ->get()
        ->map(function($item) {
            return [
                'user' => $item->user->name ?? 'User',
                'aksi' => 'mengajukan peminjaman',
                'objek' => $item->barang->nama_barang ?? 'Aset',
                'waktu' => $item->created_at->format('H:i'),
                'info' => ($item->user->user_group ?? 'Anggota'),
                'color' => 'bg-brand-cyan',
                'sort_at' => $item->created_at,
            ];
        });

    // 2. Log Pemakaian (Filter data milik user login saja)
    $logPemakaian = \App\Models\PemakaianBHP::with(['user', 'barang'])
        ->where('user_id', $userId) 
        ->latest()
        ->take(2)
        ->get()
        ->map(function($item) {
            return [
                'user' => $item->user->name ?? 'User',
                'aksi' => 'mengajukan pemakaian',
                'objek' => $item->barang->nama_barang ?? 'BHP',
                'waktu' => $item->created_at->format('H:i'),
                'info' => ($item->user->user_group ?? 'Anggota'),
                'color' => 'bg-emerald-500',
                'sort_at' => $item->created_at,
            ];
        });

    // 3. Log Pembelian (Filter data milik user login saja)
    $logPembelian = \App\Models\PengajuanPembelianBarang::with(['user'])
        ->where('user_id', $userId) 
        ->latest()
        ->take(1)
        ->get()
        ->map(function($item) {
            return [
                'user' => $item->user->name ?? 'User',
                'aksi' => 'mengajukan pembelian',
                'objek' => $item->nama_barang ?? 'Barang',
                'waktu' => $item->created_at->format('H:i'),
                'info' => ($item->user->user_group ?? 'Anggota'),
                'color' => 'bg-amber-500',
                'sort_at' => $item->created_at,
            ];
        });

    // Gabungkan dan urutkan
    $semuaAktivitas = $logPeminjaman->concat($logPemakaian)->concat($logPembelian)
        ->sortByDesc('sort_at')
        ->take(3);
?>

<?php $__env->startPush('styles'); ?>
<style>
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-main { animation: fadeInSlide 0.4s ease-out forwards; }
    
    .modern-card {
        background: white;
        border: 1px solid #eef2f6;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
    }
    
    .modern-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 91, 150, 0.08);
        border-color: #caf0f8;
    }

    .icon-box {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        transition: transform 0.3s ease;
    }
    .modern-card:hover .icon-box { transform: scale(1.1) rotate(6deg); }

    .modern-table thead th {
        background: #caf0f8;
        padding: 14px 20px;
        color: #021024;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.7rem;
    }
    .modern-table thead th:first-child { border-top-left-radius: 12px; }
    .modern-table thead th:last-child { border-top-right-radius: 12px; }
    
    .modern-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
    .modern-table tbody tr:hover { background: #f8fdff; }
    .modern-table tbody tr:last-child { border-bottom: none; }
    .modern-table td { padding: 12px 20px; }

    .item-category-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        flex-shrink: 0;
    }

    .item-category-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }

    .item-category-icon.is-aset {
        background: #eef2ff;
        color: #4f46e5;
    }

    .item-category-icon.is-bhp {
        background: #fff7ed;
        color: #f97316;
    }

    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    @media (max-width: 1024px) {
        .modern-table thead th,
        .modern-table td {
            padding: 12px 14px;
        }
    }

    @media (max-width: 768px) {
        .modern-table {
            min-width: 680px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8 pb-8 animate-main max-w-[1400px] w-full min-w-0">
  
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title text-brand-dark">Ringkasan Pengajuan</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <p class="page-section-subtitle mt-0 text-slate-500">Data diperbarui secara real-time</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
             <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 bg-white px-4 py-2.5 rounded-full border border-slate-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <?php echo e(now()->translatedFormat('l, d F Y')); ?>

        </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        
        <div class="modern-card p-5 group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-brand-light/40 to-transparent rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="flex items-start justify-between mb-4">
                    <div class="icon-box bg-brand-navy text-white shadow-lg shadow-brand-navy/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-brand-blue bg-brand-light/50 px-3 py-1.5 rounded-full uppercase tracking-wide">Tetap</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Aset</h4>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-brand-dark"><?php echo e($totalAsetCount); ?></span>
                        <span class="text-xs font-bold text-slate-400">Unit</span>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-cyan-700">
                        Unit tersedia: <?php echo e(number_format($jumlahUnitAset, 0, ',', '.')); ?> | Aktif: <?php echo e(number_format($asetAktif, 0, ',', '.')); ?>

                    </p>
                </div>
            </div>
        </div>

        
        <div class="modern-card p-5 group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-cyan-100 to-transparent rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="flex items-start justify-between mb-4">
                    <div class="icon-box bg-cyan-500 text-white shadow-lg shadow-cyan-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-cyan-600 bg-cyan-50 px-3 py-1.5 rounded-full uppercase tracking-wide">Habis Pakai</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total BHP</h4>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-brand-dark"><?php echo e($totalBHPCount); ?></span>
                        <span class="text-xs font-bold text-slate-400">Item</span>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-cyan-700">
                        Stok gudang: <?php echo e(number_format($jumlahStokBHP, 0, ',', '.')); ?> | Menipis/Habis: <?php echo e(number_format($bhpMenipisHabis, 0, ',', '.')); ?>

                    </p>
                </div>
            </div>
        </div>

        
        <div class="modern-card p-5 group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-amber-100 to-transparent rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <div class="flex items-start justify-between mb-4">
                    <div class="icon-box bg-amber-500 text-white shadow-lg shadow-amber-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-full uppercase tracking-wide">Proses</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Pengajuan Approval</h4>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-brand-dark"><?php echo e($totalPending); ?></span>
                        <span class="text-xs font-bold text-slate-400">Berkas</span>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-amber-700">
                        Peminjaman: <?php echo e(number_format($pendingPeminjaman, 0, ',', '.')); ?> | Pemakaian: <?php echo e(number_format($pendingPemakaian, 0, ',', '.')); ?> | Pembelian: <?php echo e(number_format($pendingPembelian, 0, ',', '.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 space-y-6 min-w-0">
            <div class="modern-card overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="panel-title text-brand-dark">Ketersediaan Barang</h3>
                        <p class="panel-subtitle mt-0.5">Stok terbaru aset dan BHP</p>
                    </div>
                    <a href="/ketersediaan" class="text-sm font-bold text-brand-blue hover:text-white hover:bg-brand-blue bg-brand-light/40 px-3 py-2 rounded-lg transition-all">
                        Lihat Semua →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full modern-table">
                        <thead>
                            <tr>
                                <th class="text-left w-2/5">Informasi Barang</th>
                                <th class="text-left">Kategori</th>
                                <th class="text-right">Stok Total</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ketersediaanBarang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isAset = $item->kategori_barang === 'aset';
                                $isBhpInventaris = $item->kategori_barang === 'bhp'
                                    && $item->jenis_bhp === \App\Models\BarangKantor::JENIS_BHP_INVENTARIS_KANTOR;

                                if ($isAset) {
                                    if ($item->status_barang !== 'Aktif') {
                                        $statusLabel = strtoupper($item->status_barang);
                                        $statusClass = 'bg-rose-50 text-rose-600 border-rose-100 ring-1 ring-rose-50';
                                    } elseif ($item->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN) {
                                        $statusLabel = 'TIDAK UNTUK DIPINJAMKAN';
                                        $statusClass = 'bg-slate-100 text-slate-500 border-slate-200 ring-1 ring-slate-100';
                                    } elseif ($item->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIPINJAM) {
                                        $statusLabel = 'SEDANG DIPINJAM';
                                        $statusClass = 'bg-blue-50 text-blue-600 border-blue-100 ring-1 ring-blue-50';
                                    } else {
                                        $statusLabel = 'TERSEDIA';
                                        $statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100 ring-1 ring-emerald-50';
                                    }
                                } elseif ($isBhpInventaris) {
                                    // BPP Inventaris: cek status_pinjam lebih dulu, bukan stok
                                    if ($item->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN) {
                                        $statusLabel = 'TELAH DIDISTRIBUSIKAN';
                                        $statusClass = 'bg-slate-100 text-slate-500 border-slate-200 ring-1 ring-slate-100';
                                    } elseif ($item->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIPINJAM) {
                                        $statusLabel = 'SEDANG DIPINJAM';
                                        $statusClass = 'bg-blue-50 text-blue-600 border-blue-100 ring-1 ring-blue-50';
                                    } elseif ((int) $item->stok <= 0) {
                                        $statusLabel = 'HABIS';
                                        $statusClass = 'bg-rose-50 text-rose-600 border-rose-100 ring-1 ring-rose-50';
                                    } else {
                                        $statusLabel = 'TERSEDIA';
                                        $statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100 ring-1 ring-emerald-50';
                                    }
                                } else {
                                    // BHP ATK: pakai status stok (Tersedia / Menipis / Habis)
                                    if ($item->status_stok_bhp === \App\Models\BarangKantor::STATUS_STOK_HABIS) {
                                        $statusLabel = 'HABIS';
                                        $statusClass = 'bg-rose-50 text-rose-600 border-rose-100 ring-1 ring-rose-50';
                                    } elseif ($item->status_stok_bhp === \App\Models\BarangKantor::STATUS_STOK_MENIPIS) {
                                        $statusLabel = 'MENIPIS';
                                        $statusClass = 'bg-orange-50 text-orange-600 border-orange-100 ring-1 ring-orange-50';
                                    } else {
                                        $statusLabel = 'TERSEDIA';
                                        $statusClass = 'bg-emerald-50 text-emerald-600 border-emerald-100 ring-1 ring-emerald-50';
                                    }
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="item-category-icon <?php echo e($isAset ? 'is-aset' : 'is-bhp'); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAset): ?>
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21h16M7 21V7.8c0-.53.21-1.04.59-1.41l3.8-3.8a2 2 0 0 1 2.82 0l2.2 2.2c.38.37.59.88.59 1.41V21M9.5 9.5h1m4 0h1m-6 4h1m4 0h1m-5 7v-3.5h3V21" />
                                                </svg>
                                            <?php else: ?>
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 7 4-7 4-7-4 7-4Zm7 4v8l-7 4m0-8-7-4m7 4v8m-7-4V7" />
                                                </svg>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-brand-dark"><?php echo e($item->nama_barang); ?></p>
                                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-0.5">Kode: <?php echo e($item->kode_barang); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="px-3 py-1.5 rounded-md text-xs font-bold text-slate-600 bg-slate-100">
                                        <?php echo e($item->kategori_barang == 'aset' ? 'Aset Tetap' : 'Habis Pakai'); ?>

                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="font-black text-brand-dark text-sm"><?php echo e($item->stok ?? 1); ?></span> 
                                    <span class="text-xs text-slate-400 font-medium"><?php echo e($item->satuan ?? 'Unit'); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex px-3 py-1.5 rounded-md text-xs font-bold border <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modern-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="panel-title uppercase tracking-wider">Aksi Cepat</h3>
                        <p class="panel-subtitle mt-0.5">Pilih layanan pengajuan aset & BHP</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <a href="/peminjaman" class="group p-4 rounded-2xl border border-slate-100 bg-white hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-300">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-sm group-hover:scale-110">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-700 group-hover:text-blue-700 uppercase">Peminjaman Barang Kantor</span>
                                <span class="text-xs text-slate-400 font-medium">Ajukan Peminjaman Barang Kantor</span>
                            </div>
                        </div>
                    </a>
                    
                    <a href="/pemakaian" class="group p-4 rounded-2xl border border-slate-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/30 transition-all duration-300">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-sm group-hover:scale-110">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-700 group-hover:text-emerald-700 uppercase">Pemakaian BHP</span>
                                <span class="text-xs text-slate-400 font-medium">Ajukan Pemakaian Barang Habis Pakai</span>
                            </div>
                        </div>
                    </a>
                    
                    <a href="/pembelian" class="group p-4 rounded-2xl border border-slate-100 bg-white hover:border-violet-200 hover:bg-violet-50/30 transition-all duration-300">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white flex items-center justify-center transition-all duration-300 shadow-sm group-hover:scale-110">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block text-sm font-black text-slate-700 group-hover:text-violet-700 uppercase">Pembelian Barang Kantor</span>
                                <span class="text-xs text-slate-400 font-medium">Ajukan Pengadaan Barang</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="space-y-6 min-w-0">
            <div class="modern-card p-6 bg-brand-dark text-white border-none shadow-xl relative overflow-hidden h-full">
                <div class="absolute -top-10 -right-10 w-48 h-48 bg-brand-blue/20 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="panel-title text-white tracking-wide">Aktivitas Terakhir</h3>
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-cyan opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-cyan"></span>
                        </span>
                    </div>

                    <div class="space-y-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $semuaAktivitas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex gap-4 group">
                            <div class="flex flex-col items-center">
                                <div class="w-3 h-3 rounded-full <?php echo e($log['color']); ?> shadow-[0_0_12px_rgba(72,202,228,0.6)] border border-brand-dark"></div>
                                <div class="w-0.5 h-full bg-brand-navy/50 my-2 rounded-full"></div>
                            </div>
                            <div class="pb-1">
                                <p class="text-sm text-slate-300 leading-relaxed">
                                    <span class="font-bold text-white"><?php echo e($log['user']); ?></span> <?php echo e($log['aksi']); ?> <span class="text-brand-cyan font-bold"><?php echo e($log['objek']); ?></span>
                                </p>
                                <p class="text-xs text-slate-500 mt-1.5 uppercase font-bold tracking-wider"><?php echo e($log['info']); ?> • <?php echo e($log['waktu']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-slate-500 italic">Belum ada aktivitas baru</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <a href="<?php echo e(route('riwayat.index')); ?>" class="mt-8 flex items-center justify-center w-full py-3 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-bold transition-all border border-white/10 text-brand-light">
                        Lihat Log Lengkap
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/project/resources/views/dashboard.blade.php ENDPATH**/ ?>