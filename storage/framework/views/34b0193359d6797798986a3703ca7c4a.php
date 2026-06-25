<?php $__env->startSection('title', 'Pemakaian BHP'); ?>
<?php $__env->startSection('page_title', 'Form Pengajuan Pemakaian Barang Habis Pakai'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-main { animation: fadeInSlide 0.5s ease-out forwards; }
    
    .modern-card {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
    }

    .modern-input, .modern-textarea {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        width: 100%;
        color: #334155;
    }
    
    .modern-input:focus, .modern-textarea:focus {
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

    .item-row {
        background: #f8fafc;
        border: 1.5px solid #edf2f7;
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
        transition: transform 0.2s;
    }
    .item-row:hover { transform: scale(1.005); }

    .remove-item-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        border: none;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: 0.04em;
        padding: 0.45rem 0.9rem;
        border-radius: 99px;
        text-transform: uppercase;
    }

    .barang-search-input {
        margin-bottom: 0.5rem;
    }

    .barang-select-native {
        display: none;
    }

    .searchable-select {
        position: relative;
    }

    .searchable-select-trigger {
        width: 100%;
        min-height: 3.5rem;
        padding: 0.9rem 1rem;
        padding-right: 3rem;
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        background: #fff;
        color: #334155;
        font-size: 0.95rem;
        line-height: 1.5;
        text-align: left;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }

    .searchable-select-trigger::after {
        content: "";
        position: absolute;
        right: 1rem;
        top: 50%;
        width: 0.65rem;
        height: 0.65rem;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: translateY(-70%) rotate(45deg);
        transition: transform 0.2s ease;
    }

    .searchable-select.open .searchable-select-trigger {
        border-color: #60a5fa;
        box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.12);
    }

    .modern-input.is-invalid,
    .modern-textarea.is-invalid,
    select.is-invalid,
    .searchable-select-trigger.is-invalid {
        border-color: #f43f5e !important;
        background: #fff;
        box-shadow: none !important;
    }

    .field-error {
        color: #f43f5e;
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.45rem;
    }

    .searchable-select.open .searchable-select-trigger::after {
        transform: translateY(-35%) rotate(225deg);
    }

    .searchable-select-placeholder {
        color: #94a3b8;
    }

    .searchable-select-panel {
        position: absolute;
        top: calc(100% + 0.5rem);
        left: 0;
        right: 0;
        z-index: 40;
        background: #fff;
        border: 1px solid #dbe3ee;
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        padding: 0.75rem;
    }

    .searchable-select-panel[hidden] {
        display: none;
    }

    .searchable-select-search {
        width: 100%;
        border: 1px solid #dbe3ee;
        border-radius: 12px;
        padding: 0.8rem 0.95rem;
        font-size: 0.95rem;
        outline: none;
        margin-bottom: 0.75rem;
    }

    .searchable-select-search:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.12);
    }

    .searchable-select-options {
        max-height: 260px;
        overflow-y: auto;
        border-radius: 12px;
    }

    .searchable-select-option {
        width: 100%;
        border: none;
        background: transparent;
        border-radius: 10px;
        padding: 0.85rem 0.9rem;
        text-align: left;
        color: #334155;
        font-size: 0.95rem;
        line-height: 1.5;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .searchable-select-option:hover,
    .searchable-select-option.is-active {
        background: #2563eb;
        color: #fff;
    }

    .searchable-select-empty {
        padding: 0.9rem;
        color: #94a3b8;
        font-size: 0.9rem;
        text-align: center;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 pb-10 animate-main max-w-[1400px] mx-auto">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Pengajuan Pemakaian BHP</h2>
            <p class="page-section-subtitle"> Formulir pemakaian ATK operasional kantor</p>
        </div>
        <div class="flex items-center">
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 bg-white px-4 py-2.5 rounded-full border border-slate-200 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <?php echo e(now()->translatedFormat('l, d F Y')); ?>

            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 px-5 py-4 rounded-xl shadow-sm flex items-center gap-3">
        <i class="fas fa-check-circle text-xl"></i>
        <span class="text-sm font-bold"><?php echo e(session('success')); ?></span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 px-5 py-4 rounded-xl shadow-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-xl"></i>
        <span class="text-sm font-bold"><?php echo e(session('error')); ?></span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="modern-card p-6 md:p-8">
                <form action="<?php echo e(route('pemakaian.store')); ?>" method="POST" enctype="multipart/form-data" id="pemakaianForm" class="space-y-6" novalidate>
                    <?php echo csrf_field(); ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                       <div>
                            <label class="modern-label">Nama Pengaju</label>
                            <input type="text" class="modern-input w-full px-3 py-2.5 bg-slate-100 cursor-not-allowed" 
                                value="<?php echo e(Auth::user()->name); ?> (<?php echo e(Auth::user()->user_group); ?>)" readonly>
                            <input type="hidden" name="user_id" value="<?php echo e(Auth::id()); ?>">
                        </div>

                        <div>
                            <label class="modern-label">Tanggal Pemakaian</label>
                            <input type="text" 
                                class="modern-input w-full px-3 py-2.5 bg-slate-100 cursor-not-allowed" 
                                value="<?php echo e(now()->format('d/m/Y')); ?>" readonly>
                            <input type="hidden" name="tanggal_pemakaian" value="<?php echo e(date('Y-m-d')); ?>">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-5">
                        <div class="flex items-center justify-between mb-4">
                            <label class="modern-label !mb-0">Daftar Barang yang Diminta</label>
                            <button type="button" id="addItemBtn" class="text-xs font-bold text-blue-600 hover:underline">
                                + Tambah Item
                            </button>
                        </div>
                        
                        <div id="items-container">
                            <div class="item-row">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">BHP ATK Operasional</label>
                                        <select name="items[0][kode_barang]" class="barang-select barang-select-native modern-input w-full px-3 py-2.5 bg-white">
                                            <option value="" disabled <?php echo e(!old('items.0.kode_barang', request('kode_barang')) ? 'selected' : ''); ?>>-- Pilih Barang --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $barangs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($barang->kode_barang); ?>" data-stok="<?php echo e($barang->stok); ?>" <?php echo e(old('items.0.kode_barang', request('kode_barang')) == $barang->kode_barang ? 'selected' : ''); ?>>
                                                    <?php echo e($barang->kode_barang); ?> - <?php echo e($barang->nama_barang); ?> (Stok: <?php echo e($barang->stok); ?> <?php echo e($barang->satuan); ?>)
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                        <p class="barang-err field-error <?php echo e($errors->has('items.0.kode_barang') ? '' : 'hidden'); ?>"><?php echo e($errors->first('items.0.kode_barang') ?: 'Nama barang wajib dipilih.'); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Jumlah</label>
                                        <input type="number" name="items[0][jumlah]" min="1" value="1"
                                            class="jumlah-input modern-input w-full px-3 py-2.5" placeholder="Contoh: 5" required>
                                        <p class="stok-err field-error hidden">Jumlah minimal 1.</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Satuan</label>
                                        <input type="text" class="satuan-display modern-input w-full px-3 py-2.5 bg-slate-100 cursor-not-allowed" value="-" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     <div class="mt-5 space-y-1">
                        <label class="modern-label">Alasan</label>
                        <textarea name="alasan_kebutuhan" rows="3" class="modern-textarea w-full px-3 py-2.5" placeholder="Jelaskan tujuan pemakaian barang secara detail..." required></textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="modern-label">Bukti Pendukung <span class="text-slate-400 font-normal normal-case">(Opsional)</span></label>
                        <input type="file" name="bukti_pendukung" class="modern-input w-full px-3 py-2 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-400 mt-1.5">Format: JPG, PNG. Ukuran maksimal: 2MB.</p>
                    </div>

                    <div class="pt-2 flex justify-end gap-3 border-t border-slate-100 mt-2">
                        <button type="reset" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-all">
                            Kosongkan Form
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-sky-400 text-white hover:bg-sky-500 shadow-lg shadow-sky-400/25 transition-all">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="space-y-6">
            <div class="modern-card p-5">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-history text-slate-400"></i>
                        Riwayat Pengajuan <span class="text-sm text-slate-400 font-medium">(<?php echo e($riwayat->count()); ?>)</span>
                    </h3>
                    <button type="button" id="filterToggle" class="p-2 bg-slate-50 rounded-lg text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-all">
                        <i class="fas fa-filter text-xs"></i>
                    </button>
                </div>

                <div id="filterForm" class="mb-6 p-4 bg-blue-50/50 rounded-2xl border border-blue-100 <?php echo e(request()->anyFilled(['tanggal_dari', 'tanggal_sampai', 'status']) ? '' : 'hidden'); ?>">
                    <form method="GET" action="<?php echo e(route('pemakaian.index')); ?>" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="tanggal_dari" value="<?php echo e(request('tanggal_dari')); ?>" class="modern-input text-sm py-2.5 px-3">
                            <input type="date" name="tanggal_sampai" value="<?php echo e(request('tanggal_sampai')); ?>" class="modern-input text-sm py-2.5 px-3">
                        </div>
                        <select name="status" class="modern-input text-sm py-2.5 px-3 bg-white">
                            <option value="">Semua Status Pengajuan</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="disetujui" <?php echo e(request('status') == 'disetujui' ? 'selected' : ''); ?>>Disetujui</option>
                        </select>
                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold">Cari</button>
                            <a href="<?php echo e(route('pemakaian.index')); ?>" class="px-3 py-2.5 bg-white text-slate-400 rounded-lg text-sm border border-slate-200">Reset</a>
                        </div>
                    </form>
                </div>
                
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 rounded-2xl border border-slate-50 bg-slate-50/30 hover:bg-white hover:border-blue-100 hover:shadow-md transition-all group">
                        <div class="flex justify-between items-start mb-2">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">#<?php echo e($item->kode_barang); ?></p>
                                <h4 class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                    <?php echo e($item->barang->nama_barang ?? 'Barang Terhapus'); ?>

                                </h4>
                                <p class="text-xs font-bold text-slate-700">x<?php echo e($item->jumlah); ?> <?php echo e($item->barang->satuan ?? ''); ?></p>
                            </div>
                            <?php
                                $statusColor = match($item->status) {
                                    'pending' => 'bg-amber-100 text-amber-600',
                                    'disetujui' => 'bg-emerald-100 text-emerald-600',
                                    'ditolak' => 'bg-rose-100 text-rose-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            ?>
                            <span class="status-badge <?php echo e($statusColor); ?>"><?php echo e($item->status); ?></span>
                        </div>
                        
                        <div class="flex items-center justify-end text-xs text-slate-500 pt-3 border-t border-slate-100/50">
                            <span class="flex items-center gap-1"><i class="far fa-calendar"></i> <?php echo e(\Carbon\Carbon::parse($item->tanggal_pemakaian)->format('d/m/y')); ?></span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-10">
                        <p class="text-sm font-medium text-slate-400">Belum ada riwayat ditemukan.</p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($riwayat->count() > 0): ?>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="<?php echo e(route('pemakaian.riwayat')); ?>" class="block text-center py-3 bg-sky-400 hover:bg-sky-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-sky-400/25 transition-all">
                        Buka Semua Riwayat
                    </a>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;
    const barangOptions = `<?php echo $barangs->map(fn($b) => '<option value="' . e($b->kode_barang) . '" data-stok="' . e($b->stok) . '">' . e($b->kode_barang . ' - ' . $b->nama_barang . ' (Stok: ' . $b->stok . ' ' . $b->satuan . ')') . '</option>')->implode(''); ?>`;

    const addItemBtn = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('items-container');
    const filterToggle = document.getElementById('filterToggle');
    const filterForm = document.getElementById('filterForm');

    if (filterToggle) {
        filterToggle.addEventListener('click', () => filterForm.classList.toggle('hidden'));
    }

    if (addItemBtn) {
        addItemBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const newItem = document.createElement('div');
            newItem.className = 'item-row animate-main mt-4';
            newItem.innerHTML = `
                <button type="button" class="remove-item-btn" onclick="removeItem(this)">✕</button>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">BHP ATK Operasional</label>
                        <select name="items[${itemIndex}][kode_barang]" class="barang-select barang-select-native modern-input w-full px-3 py-2.5 bg-white" required>
                            <option value="" disabled selected>-- Pilih Barang --</option>
                            ${barangOptions}
                        </select>
                        <p class="barang-err field-error hidden">Nama barang wajib dipilih.</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Jumlah</label>
                        <input type="number" name="items[${itemIndex}][jumlah]" min="1" value="1"
                            class="jumlah-input modern-input w-full px-3 py-2.5" placeholder="Contoh: 5" required>
                        <p class="stok-err field-error hidden">Jumlah minimal 1.</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Satuan</label>
                        <input type="text" class="satuan-display modern-input w-full px-3 py-2.5 bg-slate-100 cursor-not-allowed" value="-" readonly>
                    </div>
                </div>`;
            itemsContainer.appendChild(newItem);
            itemIndex++;
        });
    }

    window.removeItem = function(btn) {
        if (itemsContainer.querySelectorAll('.item-row').length > 1) {
            btn.closest('.item-row').remove();
        }
    };
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const barangItems = <?php echo \Illuminate\Support\Js::from(
        $barangs->map(function ($b) {
            return [
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'stok' => (int) $b->stok,
                'satuan' => $b->satuan,
            ];
        })->values()
    )->toHtml() ?>;

    function getItemLabel(item) {
        return `${item.kode_barang} - ${item.nama_barang} (Stok: ${item.stok} ${item.satuan ?? ''})`;
    }

    function syncSelectOptions(selectEl, items, selectedValue = '') {
        const placeholderSelected = selectedValue ? '' : 'selected';
            const options = items.map((item) => `
            <option value="${item.kode_barang}" data-stok="${item.stok}" ${selectedValue === item.kode_barang ? 'selected' : ''}>
                ${getItemLabel(item)}
            </option>
        `).join('');

        selectEl.innerHTML = `<option value="" disabled ${placeholderSelected}>--Pilih Barang--</option>${options}`;
    }

    function closeAllDropdowns(except = null) {
        document.querySelectorAll('.searchable-select').forEach((dropdown) => {
            if (dropdown !== except) {
                dropdown.classList.remove('open');
                const panel = dropdown.querySelector('.searchable-select-panel');

                if (panel) {
                    panel.hidden = true;
                }
            }
        });
    }

    function enhanceRow(row) {
        const selectEl = row.querySelector('select[name$="[kode_barang]"]');

        if (!selectEl) {
            return;
        }

        if (selectEl.dataset.searchEnhanced === 'true') {
            return;
        }

        const initialValue = selectEl.value || '';
        syncSelectOptions(selectEl, barangItems, initialValue);
        updateRowSatuan(row, initialValue);

        const dropdown = document.createElement('div');
        dropdown.className = 'searchable-select';
        dropdown.innerHTML = `
            <button type="button" class="searchable-select-trigger"></button>
            <div class="searchable-select-panel" hidden>
                <input type="text" class="searchable-select-search" placeholder="Cari nama atau kode barang...">
                <div class="searchable-select-options"></div>
            </div>
        `;

        selectEl.insertAdjacentElement('afterend', dropdown);

        const triggerEl = dropdown.querySelector('.searchable-select-trigger');
        const panelEl = dropdown.querySelector('.searchable-select-panel');
        const searchEl = dropdown.querySelector('.searchable-select-search');
        const optionsEl = dropdown.querySelector('.searchable-select-options');
        const errorEl = row.querySelector('.barang-err');

        function updateTriggerLabel() {
            const selectedItem = barangItems.find((item) => item.kode_barang === selectEl.value);

            if (!selectedItem) {
                triggerEl.innerHTML = '<span class="searchable-select-placeholder">-- Pilih Barang --</span>';
                return;
            }

            triggerEl.textContent = getItemLabel(selectedItem);
            updateRowSatuan(row, selectedItem.kode_barang);
        }

        function renderDropdownOptions(items) {
            if (!items.length) {
                optionsEl.innerHTML = '<div class="searchable-select-empty">Barang tidak ditemukan.</div>';
                return;
            }

            optionsEl.innerHTML = items.map((item) => `
                <button
                    type="button"
                    class="searchable-select-option ${selectEl.value === item.kode_barang ? 'is-active' : ''}"
                    data-value="${item.kode_barang}"
                >
                    ${getItemLabel(item)}
                </button>
            `).join('');
        }

        function filterItems(keyword) {
            const normalizedKeyword = keyword.trim().toLowerCase();

            if (normalizedKeyword === '') {
                return barangItems;
            }

            return barangItems.filter((item) =>
                item.kode_barang.toLowerCase().includes(normalizedKeyword) ||
                item.nama_barang.toLowerCase().includes(normalizedKeyword)
            );
        }

        function openDropdown() {
            closeAllDropdowns(dropdown);
            dropdown.classList.add('open');
            panelEl.hidden = false;
            searchEl.value = '';
            renderDropdownOptions(barangItems);
            setTimeout(() => searchEl.focus(), 0);
        }

        function closeDropdown() {
            dropdown.classList.remove('open');
            panelEl.hidden = true;
        }

        triggerEl.addEventListener('click', function() {
            if (dropdown.classList.contains('open')) {
                closeDropdown();
                return;
            }

            openDropdown();
        });

        searchEl.addEventListener('input', function() {
            renderDropdownOptions(filterItems(this.value));
        });

        optionsEl.addEventListener('click', function(event) {
            const optionEl = event.target.closest('.searchable-select-option');

            if (!optionEl) {
                return;
            }

            const selectedValue = optionEl.dataset.value;
            selectEl.value = selectedValue;
            syncSelectOptions(selectEl, barangItems, selectedValue);
            updateRowSatuan(row, selectedValue);
            updateTriggerLabel();
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
            closeDropdown();
        });

        document.addEventListener('click', function(event) {
            if (!dropdown.contains(event.target)) {
                closeDropdown();
            }
        });

        selectEl.addEventListener('change', function() {
            updateTriggerLabel();

            if (selectEl.value) {
                errorEl?.classList.add('hidden');
                triggerEl.classList.remove('is-invalid');
            }
        });
        row.querySelector('.jumlah-input')?.addEventListener('input', () => validateRowStok(row));
        updateTriggerLabel();
        validateRowStok(row);

        if (errorEl && !errorEl.classList.contains('hidden')) {
            triggerEl.classList.add('is-invalid');
        }

        selectEl.dataset.searchEnhanced = 'true';
    }

    function updateRowSatuan(row, kodeBarang) {
        const satuanEl = row.querySelector('.satuan-display');
        const jumlahEl = row.querySelector('.jumlah-input');
        const selectedItem = barangItems.find((item) => item.kode_barang === kodeBarang);

        if (satuanEl) {
            satuanEl.value = selectedItem?.satuan || '-';
        }

        if (jumlahEl && selectedItem) {
            jumlahEl.max = selectedItem.stok;
        }

        validateRowStok(row);
    }

    function validateRowStok(row) {
        const selectEl = row.querySelector('.barang-select');
        const jumlahEl = row.querySelector('.jumlah-input');
        const errEl = row.querySelector('.stok-err');
        const selectedItem = barangItems.find((item) => item.kode_barang === selectEl?.value);
        const stok = selectedItem ? parseInt(selectedItem.stok) || 0 : 0;
        const jumlah = parseInt(jumlahEl?.value) || 0;

        if (jumlah < 1) {
            if (errEl) {
                errEl.textContent = 'Jumlah minimal 1.';
                errEl.classList.remove('hidden');
            }

            jumlahEl?.classList.add('is-invalid');
            return false;
        }

        if (!selectEl?.value || jumlah <= stok) {
            errEl?.classList.add('hidden');
            jumlahEl?.classList.remove('is-invalid');
            return true;
        }

        if (errEl) {
            errEl.textContent = `Stok tersedia hanya ${stok}.`;
            errEl.classList.remove('hidden');
        }

        jumlahEl?.classList.add('is-invalid');

        return false;
    }

    function validateAllStok() {
        const totals = {};
        let isValid = true;

        document.querySelectorAll('#items-container .item-row').forEach((row) => {
            const selectEl = row.querySelector('.barang-select');
            const jumlahEl = row.querySelector('.jumlah-input');
            const errEl = row.querySelector('.stok-err');
            const selectedItem = barangItems.find((item) => item.kode_barang === selectEl?.value);
            const kodeBarang = selectEl?.value;
            const jumlah = parseInt(jumlahEl?.value) || 0;

            if (jumlah < 1) {
                isValid = false;
                if (errEl) {
                    errEl.textContent = 'Jumlah minimal 1.';
                    errEl.classList.remove('hidden');
                }
                jumlahEl?.classList.add('is-invalid');
                return;
            }

            jumlahEl?.classList.remove('is-invalid');

            if (!kodeBarang || !selectedItem) {
                return;
            }

            totals[kodeBarang] ??= { stok: parseInt(selectedItem.stok) || 0, jumlah: 0, rows: [] };
            totals[kodeBarang].jumlah += jumlah;
            totals[kodeBarang].rows.push({ jumlahEl, errEl });
        });

        Object.values(totals).forEach((item) => {
            if (item.jumlah > item.stok) {
                isValid = false;
                item.rows.forEach(({ jumlahEl, errEl }) => {
                    errEl.textContent = `Total pengajuan barang ini ${item.jumlah}, stok tersedia ${item.stok}.`;
                    errEl.classList.remove('hidden');
                    jumlahEl.classList.add('is-invalid');
                });
            }
        });

        return isValid;
    }

    document.querySelectorAll('#items-container .item-row').forEach(enhanceRow);

    document.getElementById('pemakaianForm')?.addEventListener('submit', function(event) {
        // Validasi: setiap baris wajib memilih barang
        let barangValid = true;
        document.querySelectorAll('#items-container .item-row').forEach((row) => {
            const selectEl  = row.querySelector('.barang-select');
            const errEl     = row.querySelector('.barang-err');
            const trigger   = row.querySelector('.searchable-select-trigger');
            if (!selectEl?.value) {
                barangValid = false;
                if (errEl) errEl.classList.remove('hidden');
                if (trigger) trigger.classList.add('is-invalid');
            } else {
                if (errEl) errEl.classList.add('hidden');
                if (trigger) trigger.classList.remove('is-invalid');
            }
        });

        if (!barangValid || !validateAllStok()) {
            event.preventDefault();
            // Scroll ke error pertama
            const firstErr = document.querySelector('.barang-err:not(.hidden), .stok-err:not(.hidden)');
            firstErr?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    document.getElementById('pemakaianForm')?.addEventListener('reset', function() {
        setTimeout(() => {
            document.querySelectorAll('#items-container .item-row').forEach((row) => {
                const selectEl = row.querySelector('.barang-select');
                const jumlahEl = row.querySelector('.jumlah-input');
                const satuanEl = row.querySelector('.satuan-display');
                const errEl = row.querySelector('.barang-err');
                const stokErrEl = row.querySelector('.stok-err');
                const trigger = row.querySelector('.searchable-select-trigger');

                if (selectEl) {
                    selectEl.value = '';
                    syncSelectOptions(selectEl, barangItems);
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (jumlahEl) {
                    jumlahEl.value = 1;
                    jumlahEl.style.borderColor = '';
                    jumlahEl.removeAttribute('max');
                }

                if (satuanEl) {
                    satuanEl.value = '-';
                }

                errEl?.classList.add('hidden');
                stokErrEl?.classList.add('hidden');
                trigger?.classList.remove('is-invalid');
            });
        }, 0);
    });

    const itemsContainer = document.getElementById('items-container');
    if (itemsContainer) {
        const observer = new MutationObserver(() => {
            itemsContainer.querySelectorAll('.item-row').forEach(enhanceRow);
        });

        observer.observe(itemsContainer, { childList: true });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/project/resources/views/pemakaian/index.blade.php ENDPATH**/ ?>