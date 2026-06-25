<div class="space-y-4 text-sm">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500"><?php echo e($item['label']); ?></div>
            <div class="mt-1 text-sm font-semibold text-slate-800"><?php echo e($item['value']); ?></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/project/resources/views/filament/admin/actions/pengajuan-detail-modal.blade.php ENDPATH**/ ?>