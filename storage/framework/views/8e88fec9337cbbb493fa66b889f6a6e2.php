<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pembelian Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0c4a6e; font-size: 18px; }
        .header h3 { margin: 5px 0; color: #0ea5e9; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 4px 0 0; color: #64748b; }

        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th { background-color: #29b6e8; color: white; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #eee; vertical-align: top; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            background: #eff6ff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>CoE SMART EV</h2>
        <h3>Laporan Pembelian Barang</h3>
        <p>Periode: <?php echo e($periodeLabel); ?></p>
        <p>Status: <?php echo e($statusLabel); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 16%;">Nama Pemohon</th>
                <th style="width: 18%;">Nama Barang</th>
                <th style="width: 10%;">Kategori</th>
                <th style="width: 10%;">Harga</th>
                <th style="width: 8%;">Jumlah</th>
                <th style="width: 12%;">Total</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $kategoriLabel = $record->kategori_barang === 'aset' ? 'Aset' : 'BHP';
                ?>
                <tr>
                    <td class="text-center"><?php echo e($record->id_pembelian_barang_kantor); ?></td>
                    <td class="text-left"><?php echo e($record->user->name ?? '-'); ?></td>
                    <td class="text-left"><?php echo e($record->nama_barang); ?></td>
                    <td class="text-center"><?php echo e($kategoriLabel); ?></td>
                    <td class="text-right">Rp <?php echo e(number_format((int) $record->perkiraan_harga, 0, ',', '.')); ?></td>
                    <td class="text-center"><?php echo e(number_format((int) $record->jumlah, 0, ',', '.')); ?></td>
                    <td class="text-right">Rp <?php echo e(number_format((int) $record->sub_total, 0, ',', '.')); ?></td>
                    <td class="text-center"><?php echo e(\Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d/m/Y')); ?></td>
                    <td class="text-center"><span class="badge"><?php echo e(ucfirst($record->status)); ?></span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /var/www/project/resources/views/filament/admin/resources/pembelian-barangs/laporan-pdf.blade.php ENDPATH**/ ?>