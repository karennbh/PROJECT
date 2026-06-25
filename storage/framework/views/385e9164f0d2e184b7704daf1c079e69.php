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
        $assetInfo = $this->getAssetInformation();
        $rows = $this->getKartuRows();
        $periodeLabel = $this->mode === 'tahun' ? 'Per Tahun' : 'Per Bulan';
    ?>

    <style>
        .kartu-aset-shell {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .kartu-aset-header {
            background: #fff;
            border: 1px solid #d9eef9;
            border-radius: 24px;
            padding: 1.75rem;
            box-shadow: 0 10px 28px rgba(14, 165, 233, 0.08);
        }

        .kartu-aset-brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .kartu-aset-brand h2 {
            margin: 0;
            color: #0c4a6e;
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .kartu-aset-brand h3 {
            margin: 0.25rem 0 0;
            color: #0ea5e9;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .kartu-aset-brand p {
            margin: 0.35rem 0 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .kartu-aset-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem 2rem;
        }

        .kartu-aset-meta-item {
            display: grid;
            grid-template-columns: 205px 16px 1fr;
            gap: 0.35rem;
            align-items: center;
        }

        .kartu-aset-meta-label {
            color: #475569;
            font-weight: 600;
            white-space: nowrap;
        }

        .kartu-aset-meta-value {
            color: #0f172a;
            font-weight: 700;
        }

        .kartu-aset-table-wrap {
            background: #fff;
            border: 1px solid #d9eef9;
            border-radius: 24px;
            padding: 1.25rem;
            box-shadow: 0 10px 28px rgba(14, 165, 233, 0.08);
        }

        .kartu-aset-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
        }

        .kartu-aset-table thead tr {
            background: linear-gradient(90deg, #30b8f4 0%, #1fa7ee 100%);
        }

        .kartu-aset-table th {
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 14px 12px;
            border: 1px solid #8dd8fb;
        }

        .kartu-aset-table td {
            padding: 12px;
            border: 1px solid #dbe7f0;
            color: #0f172a;
        }

        .kartu-aset-table tbody tr:nth-child(even) {
            background: #f8fbff;
        }

        .kartu-aset-table tbody tr:hover {
            background: #eef8ff;
        }

        .kartu-aset-note {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.35rem;
        }

        .text-right {
            text-align: right;
        }

        @media (max-width: 1024px) {
            .kartu-aset-meta {
                grid-template-columns: 1fr;
            }

            .kartu-aset-meta-item {
                grid-template-columns: 205px 16px 1fr;
            }
        }

        @media (max-width: 640px) {
            .kartu-aset-meta-item {
                grid-template-columns: minmax(170px, 1fr) 16px minmax(0, 1fr);
            }

            .kartu-aset-meta-label {
                white-space: normal;
            }
        }

        @media print {
            .kartu-aset-shell {
                gap: 1rem;
            }

            .kartu-aset-header,
            .kartu-aset-table-wrap {
                border: 1px solid #d1d5db;
                border-radius: 0;
                box-shadow: none;
                break-inside: avoid;
            }

            .kartu-aset-brand {
                text-align: center;
                margin-bottom: 1rem;
            }

            .kartu-aset-brand h2,
            .kartu-aset-brand h3,
            .kartu-aset-brand p {
                color: #111827;
            }

            .kartu-aset-meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem 1.25rem;
            }

            .kartu-aset-meta-item {
                grid-template-columns: 205px 16px 1fr;
            }

            .kartu-aset-table {
                font-size: 11px;
                border-radius: 0;
            }

            .kartu-aset-table thead tr {
                background: #e5e7eb !important;
            }

            .kartu-aset-table th {
                color: #111827;
                border-color: #d1d5db;
                padding: 8px;
            }

            .kartu-aset-table td {
                border-color: #e5e7eb;
                padding: 8px;
            }
        }
    </style>

    <div class="kartu-aset-shell">
        <section class="kartu-aset-header">
            <div class="kartu-aset-brand">
                <h2>CoE SMART EV</h2>
                <h3>Kartu Penyusutan Aset</h3>
                <p><?php echo e($assetInfo['nama_aset']); ?> - <?php echo e($assetInfo['kode_aset']); ?> | <?php echo e($periodeLabel); ?></p>
            </div>

            <div class="kartu-aset-meta">
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Nama Aset</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['nama_aset']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Kode Aset</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['kode_aset']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Tanggal Diterima</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['tanggal_diterima']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Harga Perolehan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['harga_perolehan']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Umur Ekonomis / Tahun</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['umur_ekonomis_tahun']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Umur Ekonomis / Bulan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['umur_ekonomis_bulan']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Nilai Sisa</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['nilai_sisa']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Metode Penyusutan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['metode_penyusutan']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Penyusutan per Bulan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['penyusutan_per_bulan']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Penyusutan per Tahun</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['penyusutan_per_tahun']); ?></div>
                </div>
                <div class="kartu-aset-meta-item">
                    <div class="kartu-aset-meta-label">Total Biaya Penyusutan</div>
                    <div>:</div>
                    <div class="kartu-aset-meta-value"><?php echo e($assetInfo['total_biaya_penyusutan']); ?></div>
                </div>
            </div>
        </section>

        <section class="kartu-aset-table-wrap">
            <table class="kartu-aset-table">
                <thead>
                    <tr>
                        <th><?php echo e($this->mode === 'tahun' ? 'Tahun' : 'Tanggal / Periode'); ?></th>
                        <th>Keterangan</th>
                        <th>Harga Perolehan</th>
                        <th>Penyusutan</th>
                        <th>Akumulasi Penyusutan</th>
                        <th>Nilai Buku</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div><?php echo e($row['periode']); ?></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row['periode_detail'])): ?>
                                    <div class="kartu-aset-note"><?php echo e($row['periode_detail']); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e($row['keterangan']); ?></td>
                    <td class="text-right"><?php echo e($row['harga_perolehan']); ?></td>
                    <td class="text-right"><?php echo e($row['penyusutan']); ?></td>
                            <td class="text-right"><?php echo e($row['akumulasi']); ?></td>
                            <td class="text-right"><?php echo e($row['nilai_buku']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
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
<?php /**PATH /var/www/project/resources/views/filament/admin/resources/penyusutans/kartu.blade.php ENDPATH**/ ?>