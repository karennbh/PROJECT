<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pemakaian BHP</title>
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
        <h3>Laporan Pemakaian BHP</h3>
        <p>Periode: <?php echo e($periodeLabel); ?></p>
        <p>Status: <?php echo e($statusLabel); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 18%;">Nama Pengguna</th>
                <th style="width: 22%;">Nama Barang BHP</th>
                <th style="width: 10%;">Jumlah</th>
                <th style="width: 10%;">Stok</th>
                <th style="width: 14%;">Tanggal Penggunaan</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 18%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-center"><?php echo e($record->id_pemakaian); ?></td>
                    <td class="text-left"><?php echo e($record->user->name ?? '-'); ?></td>
                    <td class="text-left"><?php echo e($record->barang->nama_barang ?? '-'); ?></td>
                    <td class="text-center"><?php echo e(number_format((int) $record->jumlah, 0, ',', '.')); ?></td>
                    <td class="text-center"><?php echo e(number_format((int) ($record->barang->stok ?? 0), 0, ',', '.')); ?></td>
                    <td class="text-center"><?php echo e(\Carbon\Carbon::parse($record->tanggal_pemakaian)->format('d/m/Y')); ?></td>
                    <td class="text-center"><span class="badge"><?php echo e(ucfirst($record->status)); ?></span></td>
                    <td class="text-left"><?php echo e($record->alasan_kebutuhan ?: '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /var/www/project/resources/views/filament/admin/resources/pemakaianbhps/laporan-pdf.blade.php ENDPATH**/ ?>