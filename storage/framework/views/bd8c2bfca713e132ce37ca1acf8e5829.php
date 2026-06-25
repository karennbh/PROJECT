<?php $__env->startSection('title', 'Semua Riwayat Pemakaian BHP'); ?>
<?php $__env->startSection('page_title', 'Riwayat Pemakaian Barang Habis Pakai'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @keyframes fadeInSlide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-main { animation: fadeInSlide 0.4s ease-out forwards; }
    .modern-card { background: white; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); border-radius: 16px; }
    .modern-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.95rem; transition: all 0.2s; width: 100%; color: #334155; }
    .modern-input:focus { background: white; border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1); outline: none; }
    .modern-label { display: block; font-size: 0.82rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em; }
    .status-badge { display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 800; line-height: 1.2; letter-spacing: 0.04em; padding: 0.45rem 0.9rem; border-radius: 999px; text-transform: uppercase; }
    .history-item { border: 1px solid #e2e8f0; border-radius: 1.25rem; background: #fff; padding: 1.25rem; transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease; }
    .history-item:hover { border-color: #bfdbfe; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); transform: translateY(-1px); }
    .history-index { width: 3.7rem; height: 3.7rem; border-radius: 0.95rem; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; flex-shrink: 0; }
    .history-code { font-size: 0.82rem; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; color: #64748b; }
    .history-title { font-size: 1rem; font-weight: 800; line-height: 1.35; color: #1e293b; }
    .history-note { font-size: 0.92rem; line-height: 1.55; color: #64748b; }
    .history-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem; font-size: 0.92rem; color: #64748b; }
    .history-meta strong { color: #334155; font-weight: 800; }
    .history-dot { color: #cbd5e1; font-weight: 900; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 pb-8 animate-main max-w-[1400px]">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Semua Riwayat Pemakaian BHP</h2>
            <p class="page-section-subtitle">Daftar lengkap semua pengajuan pemakaian barang habis pakai</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?php echo e(route('pemakaian.index')); ?>" class="text-sm font-semibold text-white bg-sky-400 px-4 py-2.5 rounded-lg shadow-lg shadow-sky-400/25 hover:bg-sky-500 transition-all"><- Kembali ke Form</a>
        </div>
    </div>

    <div class="modern-card p-5">
        <h3 class="panel-title mb-3 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>Filter Riwayat</h3>
        <form id="filterForm" method="GET" action="<?php echo e(route('pemakaian.riwayat')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div><label class="modern-label">Tanggal Dari</label><input type="date" name="tanggal_dari" value="<?php echo e(request('tanggal_dari')); ?>" class="modern-input px-3 py-2" onchange="this.form.submit()"></div>
            <div><label class="modern-label">Tanggal Sampai</label><input type="date" name="tanggal_sampai" value="<?php echo e(request('tanggal_sampai')); ?>" class="modern-input px-3 py-2" onchange="this.form.submit()"></div>
            <div>
                <label class="modern-label">Status Pengajuan</label>
                <select name="status" class="modern-input px-3 py-2 bg-white cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Status Pengajuan</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="disetujui" <?php echo e(request('status') == 'disetujui' ? 'selected' : ''); ?>>Disetujui</option>
                    <option value="ditolak" <?php echo e(request('status') == 'ditolak' ? 'selected' : ''); ?>>Ditolak</option>
                </select>
            </div>
            <div class="flex items-end gap-2"><a href="<?php echo e(route('pemakaian.riwayat')); ?>" class="w-full text-center px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Reset Filter</a></div>
        </form>
    </div>

    <div class="modern-card p-5">
        <div class="mb-4 flex items-center justify-between"><h3 class="panel-title">Daftar Riwayat (<?php echo e($riwayat->total()); ?> Item Ditemukan)</h3></div>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusClass = match($item->status) {
                        'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                        'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        'ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                ?>
                <div class="history-item animate-main">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-4">
                                <div class="history-index"><?php echo e($loop->iteration + ($riwayat->currentPage()-1) * $riwayat->perPage()); ?></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3 mb-2">
                                        <div class="min-w-0">
                                            <p class="history-code"><?php echo e($item->kode_barang ?? 'N/A'); ?></p>
                                            <h4 class="history-title"><?php echo e($item->barang->nama_barang ?? 'Barang Tidak Ditemukan'); ?></h4>
                                            <p class="history-note"><strong>Alasan:</strong> <?php echo e($item->alasan_kebutuhan); ?></p>
                                        </div>
                                        <span class="status-badge border <?php echo e($statusClass); ?>"><?php echo e($item->status); ?></span>
                                    </div>
                                    <div class="history-meta">
                                        <span><strong><?php echo e($item->user->name ?? 'USER TIDAK ADA'); ?></strong></span>
                                        <span class="history-dot">•</span>
                                        <span><strong>TANGGAL:</strong> <?php echo e(\Carbon\Carbon::parse($item->tanggal_pemakaian)->format('d/m/Y')); ?></span>
                                        <span class="history-dot">•</span>
                                        <span><strong>JUMLAH:</strong> <?php echo e($item->jumlah); ?> <?php echo e(strtoupper($item->barang->satuan ?? '')); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-12 text-slate-400"><svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg><p class="text-sm font-semibold">Tidak ada riwayat pemakaian</p><p class="text-xs mt-1">Pastikan database Anda terisi atau sesuaikan filter Anda.</p></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riwayat->hasPages()): ?><div class="mt-6 pt-4 border-t border-slate-100"><?php echo e($riwayat->appends(request()->query())->links()); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/project/resources/views/pemakaian/riwayat.blade.php ENDPATH**/ ?>