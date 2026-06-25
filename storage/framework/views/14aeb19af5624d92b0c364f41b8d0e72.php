<?php $__env->startSection('title', 'Semua Riwayat Aktivitas'); ?>
<?php $__env->startSection('page_title', 'Riwayat Keseluruhan Aktivitas'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-main { animation: fadeInSlide 0.4s ease-out forwards; }

    .modern-card {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
    }

    .modern-input {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        width: 100%;
        color: #334155;
    }

    .modern-input:focus {
        background: white;
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        outline: none;
    }

    .modern-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 pb-8 animate-main max-w-[1400px]">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Semua Riwayat Aktivitas</h2>
            <p class="page-section-subtitle">Gabungan riwayat peminjaman, pemakaian, dan pembelian dalam satu halaman.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?php echo e(route('anggota.dashboard')); ?>" class="text-sm font-semibold text-slate-600 bg-white px-4 py-2.5 rounded-lg shadow-sm border border-slate-200 hover:bg-slate-50 transition-all">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="modern-card p-5">
        <h3 class="panel-title mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            Filter Riwayat
        </h3>

        <form method="GET" action="<?php echo e(route('riwayat.index')); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="modern-label">Pengajuan</label>
                <select name="modul" class="modern-input px-3 py-2 bg-white" onchange="this.form.submit()">
                    <option value="">Semua Pengajuan</option>
                    <option value="peminjaman" <?php echo e(request('modul') == 'peminjaman' ? 'selected' : ''); ?>>Peminjaman</option>
                    <option value="pemakaian" <?php echo e(request('modul') == 'pemakaian' ? 'selected' : ''); ?>>Pemakaian</option>
                    <option value="pembelian" <?php echo e(request('modul') == 'pembelian' ? 'selected' : ''); ?>>Pembelian</option>
                </select>
            </div>
            <div>
                <label class="modern-label">Status</label>
                <select name="status" class="modern-input px-3 py-2 bg-white" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="disetujui" <?php echo e(request('status') == 'disetujui' ? 'selected' : ''); ?>>Disetujui</option>
                    <option value="ditolak" <?php echo e(request('status') == 'ditolak' ? 'selected' : ''); ?>>Ditolak</option>
                    <option value="kembali" <?php echo e(request('status') == 'kembali' ? 'selected' : ''); ?>>Kembali</option>
                </select>
            </div>
            <div>
                <label class="modern-label">Tanggal Dari</label>
                <input type="date" id="tanggal_dari" name="tanggal_dari" value="<?php echo e(request('tanggal_dari')); ?>" class="modern-input px-3 py-2" onchange="syncTanggalSampai(); this.form.submit();">
            </div>
            <div>
                <label class="modern-label">Tanggal Sampai</label>
                <input type="date" id="tanggal_sampai" name="tanggal_sampai" value="<?php echo e(request('tanggal_sampai')); ?>" min="<?php echo e(request('tanggal_dari')); ?>" class="modern-input px-3 py-2" onchange="this.form.submit()">
            </div>
            <div class="flex items-end">
                <a href="<?php echo e(route('riwayat.index')); ?>" class="w-full text-center px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    <div class="modern-card p-5">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="panel-title">
                Daftar Aktivitas (<?php echo e($riwayat->total()); ?> Item Ditemukan)
            </h3>
        </div>

        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="group p-4 rounded-xl border border-slate-100 hover:border-blue-200 hover:shadow-sm transition-all animate-main">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                                <div class="history-index"><?php echo e($loop->iteration + ($riwayat->currentPage()-1) * $riwayat->perPage()); ?></div>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2 mb-1">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-xs font-black px-2.5 py-1 rounded-full border <?php echo e($item['modul_color']); ?> uppercase">
                                                <?php echo e($item['modul_label']); ?>

                                            </span>
                                            <span class="text-sm font-black text-slate-500 uppercase">
                                                <?php echo e($item['kode']); ?>

                                            </span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800"><?php echo e($item['judul']); ?></h4>
                                    </div>
                                    <span class="text-sm font-bold px-3 py-1.5 rounded-full border <?php echo e($item['status_class']); ?> uppercase">
                                        <?php echo e($item['status_label']); ?>

                                    </span>
                                </div>

                                <p class="text-xs text-slate-600 mb-2">
                                    <span class="font-semibold text-slate-400">Keterangan:</span> <?php echo e($item['detail']); ?>

                                </p>

                                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                                    <span class="flex items-center gap-1 font-bold">
                                        <?php echo e($item['user']); ?>

                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="flex items-center gap-1">
                                        <span class="font-semibold text-slate-700"><?php echo e($item['tanggal_label']); ?>:</span>
                                        <?php echo e(\Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y')); ?>

                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-semibold text-slate-700">
                                        Jumlah: <?php echo e($item['jumlah']); ?>

                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span>
                                        Dibuat <?php echo e($item['created_at']->format('d/m/Y H:i')); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-12 text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                <p class="text-sm font-semibold">Tidak ada riwayat aktivitas</p>
                <p class="text-xs mt-1">Coba sesuaikan filter atau lakukan aktivitas baru.</p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riwayat->hasPages()): ?>
        <div class="mt-6 pt-4 border-t border-slate-100">
            <?php echo e($riwayat->appends(request()->query())->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<script>
    function syncTanggalSampai() {
        const tanggalDari = document.getElementById('tanggal_dari');
        const tanggalSampai = document.getElementById('tanggal_sampai');

        if (!tanggalDari || !tanggalSampai) {
            return;
        }

        tanggalSampai.min = tanggalDari.value || '';

        if (tanggalSampai.value && tanggalDari.value && tanggalSampai.value < tanggalDari.value) {
            tanggalSampai.value = tanggalDari.value;
        }
    }

    document.addEventListener('DOMContentLoaded', syncTanggalSampai);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/project/resources/views/riwayat/index.blade.php ENDPATH**/ ?>