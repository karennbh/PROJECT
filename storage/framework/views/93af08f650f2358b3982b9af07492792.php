<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detail Barang</title>
    <style>
        body{font-family:"Plus Jakarta Sans",system-ui,Arial,sans-serif;background:linear-gradient(180deg,#edf5ff 0%,#f8fbff 100%);margin:0;padding:28px;color:#0f172a}
        .wrap{max-width:1140px;margin:auto}
        .page-shell{background:rgba(255,255,255,.56);border:1px solid #d6e7ff;border-radius:28px;padding:26px 28px 30px;box-shadow:0 20px 45px rgba(15,23,42,.08)}
        .hero{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:22px}
        .hero-title{font-size:34px;font-weight:900;margin:0;line-height:1.1}
        .hero-sub{margin-top:10px;color:#64748b;font-size:15px;max-width:620px;line-height:1.55}
        .hero-actions{display:flex;flex-direction:column;gap:14px;align-items:flex-start}
        .card{background:#fff;border:1px solid #dbeafe;border-radius:30px;padding:30px;box-shadow:0 18px 40px rgba(15,23,42,.06)}
        .row{display:grid;grid-template-columns:250px 1fr;gap:28px}
        .img{width:250px;height:272px;border-radius:24px;border:1px solid #dbeafe;object-fit:cover;background:#f8fafc}
        .img.empty{display:flex;align-items:center;justify-content:center;font-weight:800;color:#94a3b8}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
        .panel{background:#f8fbff;border:1px solid #dbeafe;border-radius:22px;padding:18px 20px;min-height:84px}
        .k{font-size:12px;color:#5f7493;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;font-weight:800}
        .v{font-size:18px;font-weight:800;color:#0f172a;line-height:1.45}
        .v.soft{font-weight:700}
        .badge{display:inline-flex;align-items:center;padding:9px 14px;border-radius:999px;font-size:13px;font-weight:900}
        .aset{background:#dcfce7;color:#166534}
        .bhp{background:#dbeafe;color:#1e40af}
        .ok{background:#dcfce7;color:#047857}
        .warn{background:#fef3c7;color:#b45309}
        .danger{background:#fee2e2;color:#be123c}
        .muted{background:#e2e8f0;color:#475569}
        .desc{margin-top:20px;padding:20px 22px;border-radius:22px;background:#f8fbff;border:1px solid #dbeafe}
        .actions{display:flex;gap:14px;flex-wrap:wrap;margin-top:22px}
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:13px 20px;border-radius:18px;border:1px solid #bfdbfe;background:#fff;color:#0f172a;text-decoration:none;font-weight:900;box-shadow:0 8px 18px rgba(59,130,246,.08)}
        .btn.success{background:#16a34a;color:#fff;border-color:#16a34a;box-shadow:none}
        @media (max-width: 900px){.hero{flex-direction:column}.hero-actions{width:100%}}
        @media (max-width: 760px){body{padding:16px}.page-shell{padding:18px}.row{grid-template-columns:1fr}.grid{grid-template-columns:1fr}.img{width:100%;height:240px}.card{padding:20px}.hero-title{font-size:28px}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="page-shell">
        <div class="hero">
            <div>
                <h1 class="hero-title">Detail Barang</h1>
                <div class="hero-sub">Informasi ini terhubung langsung ke data sistem dan akan selalu mengikuti perubahan terbaru.</div>
            </div>
            <div class="hero-actions">
                <a href="<?php echo e($barangKantorUrl); ?>" class="btn">Kembali ke Barang Kantor</a>
                <div class="badge <?php echo e($barang->kategori_barang === 'aset' ? 'aset' : 'bhp'); ?>">
                    <?php echo e($barang->kategori_barang === 'aset' ? 'ASET TETAP' : 'BARANG HABIS PAKAI'); ?>

                </div>
            </div>
        </div>

        <div class="card">
            <div class="row">
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->foto): ?>
                        <img class="img" src="<?php echo e(asset('storage/' . $barang->foto)); ?>" alt="Foto Barang">
                    <?php else: ?>
                        <div class="img empty">Tidak ada foto</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <div class="grid">
                        <div class="panel">
                            <div class="k">Nama Barang</div>
                            <div class="v"><?php echo e($barang->nama_barang); ?></div>
                        </div>
                        <div class="panel">
                            <div class="k">Kode Barang</div>
                            <div class="v"><?php echo e($barang->kode_barang); ?></div>
                        </div>
                        <div class="panel">
                            <div class="k">Status Barang</div>
                            <div class="v">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->kategori_barang === 'aset'): ?>
                                    <span class="badge <?php echo e($barang->status_barang === 'Aktif' ? 'ok' : 'danger'); ?>">
                                        <?php echo e(strtoupper($barang->status_barang ?? '-')); ?>

                                    </span>
                                <?php else: ?>
                                    <?php
                                        $bhpStatusClass = match ($barang->status_stok_bhp) {
                                            \App\Models\BarangKantor::STATUS_STOK_HABIS => 'danger',
                                            \App\Models\BarangKantor::STATUS_STOK_MENIPIS => 'warn',
                                            default => 'ok',
                                        };
                                        $bhpStatusText = strtoupper($barang->status_stok_bhp);
                                    ?>
                                    <span class="badge <?php echo e($bhpStatusClass); ?>"><?php echo e($bhpStatusText); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="k">Ketersediaan / Stok</div>
                            <div class="v">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->kategori_barang === 'aset'): ?>
                                    <span class="badge <?php echo e($barang->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIPINJAM ? 'warn' : (($barang->status_barang ?? null) === 'Aktif' ? 'ok' : 'muted')); ?>">
                                        <?php echo e(strtoupper($barang->status_pinjam ?: 'TERSEDIA')); ?>

                                    </span>
                                <?php else: ?>
                                    <?php echo e($barang->stok ?? '-'); ?> <?php echo e($barang->satuan ?? ''); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->kategori_barang === 'aset'): ?>
                        <div class="panel">
                            <div class="k">Status Penyusutan</div>
                            <div class="v">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($penyusutan): ?>
                                    <span class="badge <?php echo e(($penyusutan->status_penyusutan ?? null) === 'Aktif' ? 'ok' : 'warn'); ?>">
                                        <?php echo e(strtoupper($penyusutan->status_penyusutan ?? 'TERSEDIA')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge muted">BELUM ADA DATA</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="panel">
                            <div class="k">Nilai Perolehan</div>
                            <div class="v"><?php echo e($barang->harga_perolehan ? 'Rp ' . number_format($barang->harga_perolehan, 0, ',', '.') : '-'); ?></div>
                        </div>
                    </div>

                    <div class="desc">
                        <div class="k">Keterangan Barang</div>
                        <div class="v soft"><?php echo e($barang->keterangan ?: 'Tidak ada keterangan.'); ?></div>
                    </div>

                    <div class="actions">
                        <a href="<?php echo e($barangKantorUrl); ?>" class="btn">Kembali</a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->kategori_barang === 'aset' && $penyusutanUrl): ?>
                            <a href="<?php echo e($penyusutanUrl); ?>" class="btn success">Lihat Detail Penyusutan</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH /var/www/project/resources/views/scan/barang.blade.php ENDPATH**/ ?>