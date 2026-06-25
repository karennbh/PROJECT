<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Barang - <?php echo e($barang->nama_barang); ?></title>
    <?php
        $kategoriLabel = $barang->kategori_barang === 'aset' ? 'Aset Tetap' : 'Barang Habis Pakai';
        $jenisLabel = $barang->kategori_barang === 'aset'
            ? ($barang->jenis_aset_label ?? '-')
            : ($barang->jenis_barang_label ?? '-');
        $statusPenggunaan = match ($barang->status_penggunaan) {
            \App\Models\BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN => 'Belum Siap Digunakan',
            \App\Models\BarangKantor::STATUS_SIAP_DIGUNAKAN => 'Siap Digunakan',
            default => '-',
        };
        $statusBarangClass = $barang->status_barang === \App\Models\BarangKantor::STATUS_AKTIF ? 'ok' : 'danger';
        $statusPinjamClass = $barang->status_pinjam === \App\Models\BarangKantor::STATUS_PINJAM_DIPINJAM ? 'warn' : 'ok';
        $stokClass = match ($barang->status_stok_bhp) {
            \App\Models\BarangKantor::STATUS_STOK_HABIS => 'danger',
            \App\Models\BarangKantor::STATUS_STOK_MENIPIS => 'warn',
            default => 'ok',
        };
    ?>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f1f7fd;
            color: #0f172a;
            font-family: "Plus Jakarta Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            padding: 28px;
        }

        .barang-detail-shell {
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
            max-width: 1180px;
            margin: 0 auto;
        }

        .barang-detail-hero,
        .barang-detail-card {
            background: #fff;
            border: 1px solid #d9eef9;
            border-radius: 24px;
            box-shadow: 0 10px 28px rgba(14, 165, 233, 0.08);
        }

        .barang-detail-hero {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1.25rem;
            align-items: center;
            padding: 1.6rem;
            overflow: hidden;
            position: relative;
        }

        .barang-detail-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(255, 255, 255, 0) 55%);
            pointer-events: none;
        }

        .barang-detail-hero > * {
            position: relative;
        }

        .barang-detail-kicker {
            color: #0284c7;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-bottom: 0.35rem;
        }

        .barang-detail-title {
            margin: 0;
            color: #0f172a;
            font-size: 1.9rem;
            line-height: 1.15;
            font-weight: 900;
        }

        .barang-detail-subtitle {
            color: #64748b;
            margin-top: 0.5rem;
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .barang-detail-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            justify-content: flex-end;
        }

        .barang-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 900;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .barang-badge.info { background: #e0f2fe; color: #0369a1; }
        .barang-badge.ok { background: #dcfce7; color: #047857; }
        .barang-badge.warn { background: #fef3c7; color: #b45309; }
        .barang-badge.danger { background: #fee2e2; color: #be123c; }
        .barang-badge.muted { background: #e2e8f0; color: #475569; }

        .barang-detail-main {
            display: grid;
            grid-template-columns: minmax(260px, 340px) 1fr;
            gap: 1.25rem;
        }

        .barang-detail-card {
            padding: 1.25rem;
        }

        .barang-photo {
            width: 100%;
            aspect-ratio: 4 / 3;
            border: 1px solid #dbeafe;
            border-radius: 20px;
            object-fit: cover;
            background: #f8fbff;
        }

        .barang-photo-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-weight: 800;
        }

        .barang-section-title {
            margin: 0 0 1rem;
            color: #0c4a6e;
            font-size: 1rem;
            font-weight: 900;
        }

        .barang-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .barang-info-item {
            min-height: 88px;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: #f8fbff;
            padding: 1rem;
        }

        .barang-info-label {
            color: #5f7493;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.72rem;
            font-weight: 900;
            margin-bottom: 0.45rem;
        }

        .barang-info-value {
            color: #0f172a;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .barang-note {
            margin-top: 0.9rem;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: #f8fbff;
            padding: 1rem;
        }

        @media (max-width: 1024px) {
            .barang-detail-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            body {
                padding: 16px;
            }

            .barang-detail-hero {
                grid-template-columns: 1fr;
            }

            .barang-detail-title {
                font-size: 1.55rem;
            }

            .barang-detail-badges {
                justify-content: flex-start;
            }

            .barang-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="barang-detail-shell">
        <section class="barang-detail-hero">
            <div>
                <div class="barang-detail-kicker">Detail Barang Kantor</div>
                <h1 class="barang-detail-title"><?php echo e($barang->nama_barang); ?></h1>
                <div class="barang-detail-subtitle">
                    Data detail ini berasal dari master Barang Kantor dan mengikuti perubahan stok,
                    status penggunaan, serta penyusutan aset.
                </div>
            </div>
            <div class="barang-detail-badges">
                <span class="barang-badge info"><?php echo e(strtoupper($kategoriLabel)); ?></span>
                <span class="barang-badge <?php echo e($statusBarangClass); ?>"><?php echo e(strtoupper($barang->status_barang ?? '-')); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->kategori_barang === 'aset'): ?>
                    <span class="barang-badge <?php echo e($statusPinjamClass); ?>"><?php echo e(strtoupper($barang->status_pinjam ?? '-')); ?></span>
                <?php else: ?>
                    <span class="barang-badge <?php echo e($stokClass); ?>">
                        <?php echo e(strtoupper($barang->status_stok_bhp)); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="barang-detail-main">
            <div class="barang-detail-card">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->foto): ?>
                    <img class="barang-photo" src="<?php echo e(asset('storage/' . $barang->foto)); ?>" alt="Foto <?php echo e($barang->nama_barang); ?>">
                <?php else: ?>
                    <div class="barang-photo barang-photo-empty">Tidak ada foto</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            </div>

            <div class="barang-detail-card">
                <h2 class="barang-section-title">Informasi Utama</h2>
                <div class="barang-info-grid">
                    <div class="barang-info-item">
                        <div class="barang-info-label">Kode Barang</div>
                        <div class="barang-info-value"><?php echo e($barang->kode_barang); ?></div>
                    </div>
                    <div class="barang-info-item">
                        <div class="barang-info-label">Nama Barang</div>
                        <div class="barang-info-value"><?php echo e($barang->nama_barang); ?></div>
                    </div>
                    <div class="barang-info-item">
                        <div class="barang-info-label">Kategori</div>
                        <div class="barang-info-value"><?php echo e($kategoriLabel); ?></div>
                    </div>
                    <div class="barang-info-item">
                        <div class="barang-info-label">Jenis Barang</div>
                        <div class="barang-info-value"><?php echo e($jenisLabel); ?></div>
                    </div>
                    <div class="barang-info-item">
                        <div class="barang-info-label">Stok</div>
                        <div class="barang-info-value"><?php echo e((int) ($barang->stok ?? 0)); ?> <?php echo e($barang->satuan ?? ''); ?></div>
                    </div>
                    <div class="barang-info-item">
                        <div class="barang-info-label">Satuan</div>
                        <div class="barang-info-value"><?php echo e($barang->satuan ?: '-'); ?></div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($barang->kategori_barang === 'aset'): ?>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Status Penggunaan</div>
                            <div class="barang-info-value"><?php echo e($statusPenggunaan); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Tanggal Diterima</div>
                            <div class="barang-info-value"><?php echo e($barang->tanggal_diterima ? $barang->tanggal_diterima->format('d/m/Y') : '-'); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Kategori Aset Tetap</div>
                            <div class="barang-info-value"><?php echo e($barang->kategoriAset?->nama_kategori_aset ?: '-'); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Umur Ekonomis</div>
                            <div class="barang-info-value"><?php echo e($barang->umur_ekonomis ? $barang->umur_ekonomis . ' Tahun' : '-'); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Harga Perolehan</div>
                            <div class="barang-info-value">Rp <?php echo e(number_format((int) $barang->harga_perolehan, 0, ',', '.')); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Nilai Residu</div>
                            <div class="barang-info-value">Rp <?php echo e(number_format((int) $barang->nilai_residu, 0, ',', '.')); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Status Penyusutan</div>
                            <div class="barang-info-value"><?php echo e($penyusutan?->status_penyusutan ?: '-'); ?></div>
                        </div>
                        <div class="barang-info-item">
                            <div class="barang-info-label">Total Penyusutan</div>
                            <div class="barang-info-value">Rp <?php echo e(number_format((int) ($penyusutan?->total_biaya_penyusutan ?? 0), 0, ',', '.')); ?></div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="barang-note">
                    <div class="barang-info-label">Keterangan</div>
                    <div class="barang-info-value"><?php echo e($barang->keterangan ?: 'Tidak ada keterangan.'); ?></div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
<?php /**PATH /var/www/project/resources/views/public/barang-detail.blade.php ENDPATH**/ ?>