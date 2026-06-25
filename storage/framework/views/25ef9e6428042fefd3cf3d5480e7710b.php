<?php $__env->startSection('title', 'Mencari Ketersediaan Barang'); ?>
<?php $__env->startSection('page_title', 'Ketersediaan Aset & BHP'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-main { animation: fadeInSlide 0.4s ease-out forwards; }
    
    .glass-card {
        background: white;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        border-radius: 20px;
    }

    .custom-table thead tr {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    .custom-table th {
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 16px 20px;
    }
    .custom-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f8fafc;
    }
    .custom-table tbody tr:hover {
        background-color: #fcfeff;
        transform: scale(1.002);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        z-index: 10;
        position: relative;
    }

    .item-info {
        min-width: 0;
        width: 100%;
        max-width: 580px;
    }

    .item-name {
        display: block;
        min-height: 1.5rem;
        line-height: 1.25rem;
        margin-bottom: 2px;
    }

    .item-code {
        display: block;
        margin-top: 0.125rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8 pb-10 animate-main max-w-[1400px]">

    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Ketersediaan Barang</h2>
            <p class="text-sm text-slate-500 mt-1">Monitoring aset dan BHP</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 bg-white px-4 py-2.5 rounded-full border border-slate-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <?php echo e(now()->translatedFormat('l, d F Y')); ?>

        </div>
    </div>

    
    <div class="relative overflow-hidden rounded-2xl bg-slate-900 shadow-xl shadow-slate-200 p-6">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-emerald-500 opacity-10 blur-3xl"></div>

        <form id="filterForm" action="<?php echo e(route('ketersediaan.index')); ?>" method="GET" class="relative z-10 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-5 items-end">
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wider">Kategori</label>
                <select name="jenis" onchange="this.form.submit()" class="w-full bg-slate-800 text-white border border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                    <option value="all">Semua Kategori</option>
                    <option value="aset" <?php echo e(request('jenis') == 'aset' ? 'selected' : ''); ?>>Aset Tetap</option>
                    <option value="bhp_inventaris" <?php echo e(request('jenis') == 'bhp_inventaris' ? 'selected' : ''); ?>>BHP BPP Inventaris Kantor</option>
                    <option value="bhp_atk" <?php echo e(request('jenis') == 'bhp_atk' ? 'selected' : ''); ?>>BHP ATK Operasional</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wider">Kondisi Barang</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-800 text-white border border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="ready"             <?php echo e(request('status') == 'ready'             ? 'selected' : ''); ?>>Tersedia</option>
                    <option value="menipis"           <?php echo e(request('status') == 'menipis'           ? 'selected' : ''); ?>>Menipis</option>
                    <option value="dipinjam"          <?php echo e(request('status') == 'dipinjam'          ? 'selected' : ''); ?>>Sedang Dipinjam</option>
                    <option value="habis"             <?php echo e(request('status') == 'habis'             ? 'selected' : ''); ?>>Habis</option>
                    <option value="didistribusikan"   <?php echo e(request('status') == 'didistribusikan'   ? 'selected' : ''); ?>>Telah Didistribusikan</option>
                    <option value="tidak_dipinjamkan" <?php echo e(request('status') == 'tidak_dipinjamkan' ? 'selected' : ''); ?>>Tidak untuk Dipinjamkan</option>
                    <option value="unavailable"       <?php echo e(request('status') == 'unavailable'       ? 'selected' : ''); ?>>Tidak Tersedia (Habis/Non-Aktif)</option>
                </select>
            </div>

            <div class="md:col-span-1 lg:col-span-2">
                <label class="block text-sm font-bold text-slate-300 mb-2 uppercase tracking-wider">Pencarian Cepat</label>
                <div class="relative">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                           oninput="debounceSubmit()"
                           class="w-full bg-slate-800 text-white border border-slate-700 rounded-xl px-4 py-3 pl-10 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                           placeholder="Ketik nama atau kode barang...">
                    <div class="absolute left-3 top-3.5 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>
        </form>
    </div>

    
    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full custom-table border-collapse text-left">
                <thead>
                    <tr>
                        <th class="w-[40%] pl-6">Nama Barang</th>
                        <th class="w-[15%]">Kategori</th>
                        <th class="w-[15%] text-left">Stok / Status</th>
                        <th class="w-[15%] text-center">Kondisi</th>
                        <th class="w-[15%] text-center pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $barangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isAset = strtolower($barang->kategori_barang) == 'aset';
                            $isBhpAtk = $barang->kategori_barang === 'bhp'
                                && $barang->jenis_bhp === \App\Models\BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR;
                            $isBhpInventaris = $barang->kategori_barang === 'bhp'
                                && $barang->jenis_bhp === \App\Models\BarangKantor::JENIS_BHP_INVENTARIS_KANTOR;
                            $canUse = $isBhpAtk;
                            $canBorrow = $isAset || $isBhpInventaris;
                            
                            $asetSiapPinjam = $isAset
                                && $barang->status_barang == 'Aktif'
                                && $barang->stok > 0
                                && $barang->status_pinjam == 'Tersedia'
                                && $barang->status_penggunaan === \App\Models\BarangKantor::STATUS_SIAP_DIGUNAKAN
                                && filled($barang->tanggal_diterima);
                                
                            $bhpTersedia = ! $isAset
                                && $barang->status_barang == 'Aktif'
                                && $barang->stok > 0;
                                
                            $tersedia = $isAset ? $asetSiapPinjam : $bhpTersedia;
                            $statusText = $isAset ? ($barang->status_barang ?? 'TIDAK TERDATA') : ($barang->stok . ' ' . ($barang->satuan ?? 'Unit'));
                            
                            if ($isAset) {
                                $kondisiText = $barang->status_barang !== 'Aktif'
                                    ? strtoupper($barang->status_barang ?? 'TIDAK AKTIF')
                                    : match ($barang->status_pinjam) {
                                        \App\Models\BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN => 'TIDAK UNTUK DIPINJAMKAN',
                                        \App\Models\BarangKantor::STATUS_PINJAM_DIPINJAM          => 'SEDANG DIPINJAM',
                                        default                                                    => 'TERSEDIA',
                                    };
                            } else {
                                if ($isBhpInventaris) {
                                    if ($barang->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN) {
                                        $kondisiText = 'TELAH DIDISTRIBUSIKAN';
                                    } elseif ($barang->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIPINJAM) {
                                        $kondisiText = 'SEDANG DIPINJAM';
                                    } elseif ((int) $barang->stok <= 0) {
                                        $kondisiText = 'HABIS';
                                    } else {
                                        $kondisiText = 'TERSEDIA';
                                    }
                                } else {
                                    // BHP ATK: pakai status stok model (Tersedia / Menipis / Habis)
                                    $kondisiText = strtoupper($barang->status_stok_bhp);
                                }
                            }
                            
                            // Warna badge kondisi: emerald=tersedia, orange=menipis, blue=sedang dipinjam, rose=habis, gray=didistribusikan/tidak dipinjamkan
                            $colorClass = match($kondisiText) {
                                'MENIPIS'                     => 'orange',
                                'SEDANG DIPINJAM'             => 'blue',
                                'HABIS',
                                'TIDAK AKTIF'                 => 'rose',
                                'TELAH DIDISTRIBUSIKAN',
                                'TIDAK UNTUK DIPINJAMKAN'     => 'gray',
                                default                       => 'emerald',
                            };
                            // Warna dot & teks stok: ikut kondisi — hijau hanya jika benar-benar tersedia
                            $stokColorClass = match($kondisiText) {
                                'TERSEDIA'               => 'emerald',
                                'MENIPIS'                => 'amber',
                                default                  => 'rose',
                            };
                        ?>
                        <tr>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center shadow-sm 
                                        <?php echo e($isAset ? 'bg-indigo-50 text-indigo-600' : 'bg-orange-50 text-orange-600'); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAset): ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <?php else: ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="item-info flex flex-col justify-start">
                                        <h4 class="item-name text-sm font-bold text-slate-800 break-words"><?php echo e($barang->nama_barang); ?></h4>
                                        <span class="item-code text-sm text-slate-400 font-semibold tracking-wide">#<?php echo e($barang->kode_barang); ?></span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-5">
                                <div class="flex flex-col items-start gap-1">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAset): ?>
                                        <span class="inline-flex items-center text-sm font-bold px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-tight">
                                            Aset Tetap
                                        </span>
                                        <span class="text-xs font-semibold text-slate-400 pl-1"></span>
                                    <?php elseif($isBhpInventaris): ?>
                                        <span class="inline-flex items-center text-sm font-bold px-3 py-1.5 rounded-xl bg-orange-50 text-orange-600 border border-orange-100 uppercase tracking-tight">
                                            BHP BPP Inventaris Kantor
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center text-sm font-bold px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-tight">
                                            BHP ATK
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>

                            <td class="px-4 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="relative flex h-2 w-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tersedia): ?>
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <span class="relative inline-flex rounded-full h-2 w-2 <?php echo e($tersedia ? 'bg-emerald-500' : 'bg-rose-500'); ?>"></span>
                                    </span>
                                    <span class="text-sm font-bold <?php echo e($tersedia ? 'text-emerald-600' : 'text-rose-600'); ?> uppercase tracking-wide">
                                        <?php echo e($statusText); ?>

                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-5 text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kondisiText === 'MENIPIS'): ?>
                                    <span class="inline-flex items-center justify-center min-w-[140px] text-sm font-bold py-2.5 px-4 rounded-full border tracking-wide bg-orange-50 text-orange-600 border-orange-200">
                                        <?php echo e($kondisiText); ?>

                                    </span>
                                <?php elseif($kondisiText === 'TERSEDIA'): ?>
                                    <span class="inline-flex items-center justify-center min-w-[140px] text-sm font-bold py-2.5 px-4 rounded-full border tracking-wide bg-emerald-50 text-emerald-600 border-emerald-100">
                                        <?php echo e($kondisiText); ?>

                                    </span>
                                <?php elseif($kondisiText === 'TELAH DIDISTRIBUSIKAN' || $kondisiText === 'TIDAK UNTUK DIPINJAMKAN'): ?>
                                    <span class="inline-flex items-center justify-center min-w-[140px] text-sm font-bold py-2.5 px-4 rounded-full border tracking-wide bg-slate-100 text-slate-500 border-slate-200">
                                        <?php echo e($kondisiText); ?>

                                    </span>
                                <?php elseif($kondisiText === 'SEDANG DIPINJAM'): ?>
                                    <span class="inline-flex items-center justify-center min-w-[140px] text-sm font-bold py-2.5 px-4 rounded-full border tracking-wide bg-blue-50 text-blue-600 border-blue-200">
                                        <?php echo e($kondisiText); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center min-w-[140px] text-sm font-bold py-2.5 px-4 rounded-full border tracking-wide bg-rose-50 text-rose-600 border-rose-100">
                                        <?php echo e($kondisiText); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>

                            <td class="px-6 py-5 text-center pr-6">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kondisiText === 'TERSEDIA'): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canBorrow): ?>
                                        <a href="<?php echo e(route('peminjaman.index', ['kode_barang' => $barang->kode_barang])); ?>" 
                                        class="inline-flex items-center justify-center min-w-[140px] px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-2xl transition-all shadow-sm">
                                            Pinjam
                                        </a>
                                    <?php elseif($canUse): ?>
                                        <a href="<?php echo e(route('pemakaian.index', ['kode_barang' => $barang->kode_barang])); ?>" 
                                        class="inline-flex items-center justify-center min-w-[140px] px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-2xl transition-all shadow-sm">
                                            Pakai
                                        </a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php elseif($kondisiText === 'MENIPIS' && $canUse): ?>
                                    <a href="<?php echo e(route('pemakaian.index', ['kode_barang' => $barang->kode_barang])); ?>" 
                                    class="inline-flex items-center justify-center min-w-[140px] px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-2xl transition-all shadow-sm">
                                        Pakai
                                    </a>
                                <?php else: ?>
                                    <button disabled class="inline-flex items-center justify-center min-w-[140px] px-4 py-3 bg-slate-100 text-slate-400 text-sm font-bold rounded-2xl cursor-not-allowed border border-slate-200">
                                        Tidak Tersedia
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center text-slate-400 font-bold uppercase tracking-widest">
                                Data tidak ditemukan
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-5 border-t border-slate-50 bg-slate-50/30">
            <?php echo e($barangs->links()); ?>

        </div>
    </div>
</div>

<script>
    let timer;
    function debounceSubmit() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 800);
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/project/resources/views/ketersediaan/index.blade.php ENDPATH**/ ?>