<!DOCTYPE html>
<html>
<head>
    <title>Laporan Jurnal Umum</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #0c4a6e; font-size: 18px; }
        .header h3 { margin: 5px 0; color: #0ea5e9; font-size: 14px; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #29b6e8; color: white; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #eee; }
        
        /* Style Tabel Kedua (Overview) */
        .table-overview th { background-color: #f9fafb; color: #4b5563; text-align: left; }
        .badge { background-color: #ecfdf5; color: #065f46; padding: 3px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; border: 1px solid #d1fae5; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h2>CoE SMART EV</h2>
        <h3>LAPORAN JURNAL UMUM</h3>
        <p>Periode: <?php echo e(\Carbon\Carbon::parse($periode_awal)->translatedFormat('F Y')); ?> - <?php echo e(\Carbon\Carbon::parse($periode_akhir)->translatedFormat('F Y')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Akun</th>
                <th>Keterangan</th>
                <th>Reff</th>
                <th>Debit (Rp)</th>
                <th>Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jurnals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jurnal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    // Logika Nomor Bukti Dinamis (Reff)
                    $nomorBukti = $jurnal->reff_transaksi;
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $jurnal->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loop->first): ?> <?php echo e(\Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e($detail->coa?->kode_akun ?? $detail->kode_akun); ?></td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->nominal_debit > 0): ?>
                            <?php echo e($detail->coa->nama_akun ?? '-'); ?>

                        <?php else: ?>
                            <span style="padding-left: 20px;"><?php echo e($detail->coa->nama_akun ?? '-'); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loop->first): ?> <?php echo e($nomorBukti); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?php echo e($detail->nominal_debit > 0 ? 'Rp' . number_format($detail->nominal_debit, 0, ',', '.') : ''); ?>

                    </td>
                    <td class="text-right">
                        <?php echo e($detail->nominal_kredit > 0 ? 'Rp' . number_format($detail->nominal_kredit, 0, ',', '.') : ''); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <tr style="background-color: #f9fafb;">
                <td colspan="4" class="text-right font-bold">TOTAL</td>
                <td class="text-right font-bold">Rp<?php echo e(number_format($totalDebit, 0, ',', '.')); ?></td>
                <td class="text-right font-bold">Rp<?php echo e(number_format($totalKredit, 0, ',', '.')); ?></td>
            </tr>
        </tbody>
    </table>

    <hr style="border: 1px solid #eee; margin-bottom: 20px;">

    <table class="table-overview">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nomor Bukti</th>
                <th>Deskripsi</th>
                <th>Tipe Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $daftarTransaksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $buktiTransaksi = $item->reff_transaksi;
            ?>
            <tr>
                <td class="text-center"><?php echo e(\Carbon\Carbon::parse($item->tanggal)->format('d/m/Y')); ?></td>
                <td><?php echo e($buktiTransaksi ?? '-'); ?></td>
                <td><?php echo e($item->deskripsi ?? 'Pembelian (-)'); ?></td>
                <td class="text-center">
                    <span class="badge"><?php echo e(strtoupper(str_replace('_', ' ', $item->tipe_transaksi))); ?></span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

</body>
</html>

<?php /**PATH /var/www/project/resources/views/filament/admin/resources/jurnal-umums/widgets/jurnal-umum-pdf.blade.php ENDPATH**/ ?>