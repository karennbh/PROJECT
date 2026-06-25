<?php if (isset($component)) { $__componentOriginald2aa9f7b74553621bdcc3c69267ff328 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald2aa9f7b74553621bdcc3c69267ff328 = $attributes; } ?>
<?php $component = Filament\View\LegacyComponents\PageComponent::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Filament\View\LegacyComponents\PageComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $barang = $this->record;
        $penyusutan = $barang->kategori_barang === 'aset'
            ? $barang->penyusutans()->latest('id_penyusutan')->first()
            : null;

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
        .barang-detail-shell {
            display: flex;
            flex-direction: column;
            gap: 1.35rem;
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

        .barang-qr-box {
            display: grid;
            grid-template-columns: 104px 1fr;
            gap: 0.9rem;
            align-items: center;
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 18px;
            background: #f8fbff;
            border: 1px solid #dbeafe;
        }

        .barang-qr-box img {
            width: 104px;
            height: 104px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid #dbeafe;
            padding: 0.35rem;
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

        .barang-action-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .barang-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0.65rem 1rem;
            border-radius: 14px;
            border: 1px solid #bae6fd;
            background: #fff;
            color: #075985;
            font-weight: 900;
            text-decoration: none;
        }

        .barang-action.primary {
            background: #0ea5e9;
            color: #fff;
            border-color: #0ea5e9;
        }

        @media (max-width: 1024px) {
            .barang-detail-main {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .barang-detail-hero {
                grid-template-columns: 1fr;
            }

            .barang-detail-badges {
                justify-content: flex-start;
            }

            .barang-info-grid,
            .barang-qr-box {
                grid-template-columns: 1fr;
            }
        }
    </style>

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

                <div class="barang-qr-box">
                    <img src="<?php echo e($barang->barcode_qr_image_url); ?>" alt="QR <?php echo e($barang->barcode); ?>">
                    <div>
                        <div class="barang-info-label">Barcode / QR</div>
                        <div class="barang-info-value"><?php echo e($barang->barcode ?: '-'); ?></div>
                        <div style="color:#64748b;font-size:.86rem;margin-top:.35rem;line-height:1.45">
                            QR mengarah ke detail barang ini di sistem.
                        </div>
                    </div>
                </div>

                <div class="barang-action-row">
                    <a class="barang-action" href="<?php echo e(\App\Filament\Admin\Resources\BarangKantors\BarangKantorResource::getUrl('index')); ?>">
                        Kembali ke Barang Kantor
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($penyusutan): ?>
                        <a class="barang-action primary" href="<?php echo e(\App\Filament\Admin\Resources\Penyusutans\PenyusutanResource::getUrl('kartu', ['record' => $penyusutan, 'from' => 'barang-kantor'])); ?>">
                            Kartu Penyusutan
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald2aa9f7b74553621bdcc3c69267ff328)): ?>
<?php $attributes = $__attributesOriginald2aa9f7b74553621bdcc3c69267ff328; ?>
<?php unset($__attributesOriginald2aa9f7b74553621bdcc3c69267ff328); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald2aa9f7b74553621bdcc3c69267ff328)): ?>
<?php $component = $__componentOriginald2aa9f7b74553621bdcc3c69267ff328; ?>
<?php unset($__componentOriginald2aa9f7b74553621bdcc3c69267ff328); ?>
<?php endif; ?>
<?php /**PATH /var/www/project/resources/views/filament/admin/resources/barang-kantors/detail.blade.php ENDPATH**/ ?>