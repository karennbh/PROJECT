<?php $__env->startSection('title', 'Pembelian Barang'); ?>
<?php $__env->startSection('page_title', 'Form Pengajuan Pembelian Barang'); ?>

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

    .modern-input, .modern-textarea, select {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.2s;
        width: 100%;
        color: #334155;
    }
    
    .modern-input:focus, .modern-textarea:focus, select:focus {
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

    /* Style tambahan untuk item dinamis */
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

    .modern-input.is-invalid,
    .modern-textarea.is-invalid,
    select.is-invalid {
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
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 pb-10 animate-main max-w-[1400px] mx-auto">

    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Pengajuan Pembelian</h2>
            <p class="page-section-subtitle">Form pengajuan pengadaan aset atau BHP baru</p>
        </div>
        <div class="flex items-center">
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 bg-white px-4 py-2.5 rounded-full border border-slate-200 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <?php echo e(now()->translatedFormat('l, d F Y')); ?>

            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2">
            <div class="modern-card p-6 md:p-8">
                <form action="<?php echo e(route('pembelian.store')); ?>" method="POST" enctype="multipart/form-data" id="pembelianForm" class="space-y-5" novalidate>
                    <?php echo csrf_field(); ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="modern-label">Nama Pengaju</label>
                            <input type="text" class="modern-input px-3 py-2.5 bg-slate-100 cursor-not-allowed" 
                                value="<?php echo e(Auth::user()->name); ?> (<?php echo e(Auth::user()->user_group); ?>)" readonly>
                        </div>
                        <div>
                            <label class="modern-label">Tanggal Pengajuan</label>
                            <input type="text" class="modern-input w-full px-3 py-2.5 bg-slate-100 cursor-not-allowed" 
                                value="<?php echo e(now()->format('d/m/Y')); ?>" readonly>
                            <input type="hidden" name="tanggal_pengajuan" value="<?php echo e(date('Y-m-d')); ?>">
                        </div>
                    </div>

                    <div class="border-t pt-5">
                        <div class="flex justify-between items-center mb-4">
                            <label class="modern-label !mb-0">Daftar Barang yang Diajukan</label>
                            <button type="button" id="addItemBtn" class="text-xs font-bold text-blue-600 hover:underline">
                                + Tambah Item
                            </button>
                        </div>

                        <div id="items-container">
                            
                            <div class="item-row animate-main" data-index="0">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Nama Barang</label>
                                        <input type="text" name="items[0][nama_barang]" class="nama-barang-input modern-input px-3 py-2 <?php echo e($errors->has('items.0.nama_barang') ? 'is-invalid' : ''); ?>" placeholder="Nama Barang" value="<?php echo e(old('items.0.nama_barang')); ?>" minlength="3" maxlength="50" required>
                                        <p class="nama-barang-err field-error <?php echo e($errors->has('items.0.nama_barang') ? '' : 'hidden'); ?>"><?php echo e($errors->first('items.0.nama_barang') ?: 'Nama barang wajib diisi.'); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Kategori</label>
                                        <select name="items[0][kategori_barang]" class="modern-input px-3 py-2 bg-white" required>
                                            <option value="aset">Aset Tetap</option>
                                            <option value="bhp">BHP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Harga Satuan (Rp)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold pointer-events-none">Rp</span>
                                            <input type="text" inputmode="numeric"
                                                class="modern-input pl-8 pr-3 py-2 input-harga-display"
                                                placeholder="1.000" autocomplete="off">
                                            <input type="hidden" name="items[0][perkiraan_harga]" class="input-harga">
                                        </div>
                                        <p class="text-rose-500 text-xs font-semibold mt-1 harga-error hidden">Harga tidak boleh kurang dari 0.</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Jumlah Unit</label>
                                        <input type="number" name="items[0][jumlah]" class="modern-input px-3 py-2 input-jumlah" value="1" min="1" required>
                                        <p class="jumlah-err field-error hidden">Jumlah minimal 1.</p>
                                    </div>
                                </div>
                                <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Link Pembelian / Referensi</label>
                                    <input type="url" name="items[0][link_barang]" class="modern-input px-3 py-2" placeholder="https://...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50/50 p-4 rounded-xl border border-dashed border-blue-200 flex justify-between items-center transition-all">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-bold text-slate-500 tracking-wider uppercase">Total Seluruh Pengajuan:</span>
                        </div>
                        <span id="display_sub_total" class="text-base font-black text-blue-600">Rp 0</span>
                    </div>

                    <div>
                        <label class="modern-label">Alasan</label>
                        <textarea name="alasan" rows="3" class="modern-textarea px-3 py-2.5" placeholder="Jelaskan mengapa barang-barang ini dibutuhkan..." required></textarea>
                    </div>

                    <div>
                        <label class="modern-label">Bukti Pendukung</label>
                        <input type="file" name="bukti_pendukung" required class="modern-input w-full px-3 py-2 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-400 mt-1.5">Format: JPG, PNG. Ukuran maksimal: 2MB.</p>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                        <button type="reset" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition-all">
                            Kosongkan Form
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-sky-400 text-white hover:bg-sky-500 shadow-lg shadow-sky-400/25 transition-all">
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
                    <button type="button" id="filterToggle" class="p-2 bg-slate-50 rounded-lg text-slate-500 hover:bg-blue-50 transition-all">
                        <i class="fas fa-filter text-xs"></i>
                    </button>
                </div>

                <div id="filterForm" class="mb-6 p-4 bg-blue-50/50 rounded-2xl border border-blue-100 <?php echo e(request()->anyFilled(['tanggal_dari', 'tanggal_sampai', 'status']) ? '' : 'hidden'); ?>">
                    <form method="GET" action="<?php echo e(route('pembelian.index')); ?>" class="space-y-3">
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
                            <a href="<?php echo e(route('pembelian.index')); ?>" class="px-3 py-2.5 bg-white text-slate-400 rounded-lg text-sm border border-slate-200">Reset</a>
                        </div>
                    </form>
                </div>
                
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 rounded-2xl border border-slate-50 bg-slate-50/30 hover:bg-white hover:border-blue-100 transition-all group animate-main">
                        <div class="flex justify-between items-start mb-2">
                            <div class="space-y-1">
                                <p class="text-xs font-black text-blue-500 uppercase tracking-widest"><?php echo e($item->kategori_barang); ?></p>
                                <h4 class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                    <?php echo e($item->nama_barang); ?>

                                </h4>
                                <p class="text-xs font-bold text-slate-700">x<?php echo e($item->jumlah); ?> Unit</p>
                                <p class="text-sm text-blue-600 font-bold">Rp <?php echo e(number_format($item->sub_total, 0, ',', '.')); ?></p>
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
                            <span class="flex items-center gap-1"><i class="far fa-calendar"></i> <?php echo e(\Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d/m/y')); ?></span>
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
                    <a href="<?php echo e(route('pembelian.riwayat')); ?>" class="block text-center py-3 bg-sky-400 hover:bg-sky-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-sky-400/25 transition-all">
                        Buka Semua Riwayat
                    </a>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let itemIndex = 1;

// ─── Format & parse helpers ───────────────────────────────────────────────────
function formatRibuan(val) {
    // Hapus semua karakter selain angka
    const num = val.replace(/\D/g, '');
    if (!num) return '';
    return parseInt(num, 10).toLocaleString('id-ID');
}

function parseRibuan(str) {
    // Kembalikan nilai numerik bersih dari string berformat
    return parseInt(str.replace(/\D/g, '') || '0', 10);
}

// ─── Kalkulasi grand total ────────────────────────────────────────────────────
function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const harga  = parseRibuan(row.querySelector('.input-harga').value);
        const jumlah = parseInt(row.querySelector('.input-jumlah').value || '0', 10);
        grandTotal  += harga * jumlah;
    });

    document.getElementById('display_sub_total').innerText =
        new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', maximumFractionDigits: 0
        }).format(grandTotal);
}

// ─── Pasang event format+validasi pada satu input display ────────────────────
function attachHargaEvents(displayInput) {
    const hiddenInput = displayInput.closest('div.relative').querySelector('.input-harga');
    const errorEl    = displayInput.closest('div').parentElement.querySelector('.harga-error');

    displayInput.addEventListener('input', function () {
        // Hanya izinkan angka
        const raw = this.value.replace(/\D/g, '');
        const num = raw ? parseInt(raw, 10) : 0;

        // Format tampilan
        this.value = raw ? parseInt(raw, 10).toLocaleString('id-ID') : '';

        // Simpan nilai bersih ke hidden
        hiddenInput.value = raw ? num : '';

        // Validasi negatif (tidak bisa lewat keyboard tapi jaga-jaga)
        if (num < 0) {
            displayInput.classList.add('border-rose-400');
            errorEl && errorEl.classList.remove('hidden');
            hiddenInput.value = '';
        } else {
            displayInput.classList.remove('border-rose-400');
            errorEl && errorEl.classList.add('hidden');
        }

        calculateGrandTotal();
    });

    // Blokir karakter non-angka (termasuk minus)
    displayInput.addEventListener('keydown', function (e) {
        const allowed = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'];
        if (allowed.includes(e.key)) return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    // Pada paste: bersihkan dan format
    displayInput.addEventListener('paste', function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        const raw    = pasted.replace(/\D/g, '');
        if (!raw) return;
        const num = parseInt(raw, 10);
        this.value        = num.toLocaleString('id-ID');
        hiddenInput.value = num;
        calculateGrandTotal();
    });
}

// ─── HTML template baris item baru ───────────────────────────────────────────
function buildItemHTML(idx) {
    return `
        <button type="button" class="remove-item-btn" onclick="this.parentElement.remove(); calculateGrandTotal();">✕</button>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Nama Barang</label>
                <input type="text" name="items[${idx}][nama_barang]" class="nama-barang-input modern-input px-3 py-2" placeholder="Nama Barang" minlength="3" maxlength="50" required>
                <p class="nama-barang-err field-error hidden">Nama barang wajib diisi.</p>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Kategori</label>
                <select name="items[${idx}][kategori_barang]" class="modern-input px-3 py-2 bg-white" required>
                    <option value="aset">Aset Tetap</option>
                    <option value="bhp">BHP</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Harga Satuan (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold pointer-events-none">Rp</span>
                    <input type="text" inputmode="numeric"
                        class="modern-input pl-8 pr-3 py-2 input-harga-display"
                        placeholder="1.000" autocomplete="off">
                    <input type="hidden" name="items[${idx}][perkiraan_harga]" class="input-harga">
                </div>
                <p class="text-rose-500 text-xs font-semibold mt-1 harga-error hidden">Harga tidak boleh kurang dari 0.</p>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Jumlah Unit</label>
                <input type="number" name="items[${idx}][jumlah]" class="modern-input px-3 py-2 input-jumlah" value="1" min="1" required>
                <p class="jumlah-err field-error hidden">Jumlah minimal 1.</p>
            </div>
        </div>
        <div>
            <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Link Pembelian / Referensi</label>
            <input type="url" name="items[${idx}][link_barang]" class="modern-input px-3 py-2" placeholder="https://...">
        </div>`;
}

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const addItemBtn     = document.getElementById('addItemBtn');
    const itemsContainer = document.getElementById('items-container');
    const filterToggle   = document.getElementById('filterToggle');
    const formEl         = document.getElementById('pembelianForm');

    function setNamaBarangError(input, message = '') {
        const errorEl = input.closest('div')?.querySelector('.nama-barang-err');

        if (message) {
            input.classList.add('is-invalid');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
            return;
        }

        input.classList.remove('is-invalid');
        errorEl?.classList.add('hidden');
    }

    function validateNamaBarang(input) {
        const value = input.value.trim();

        if (!value) {
            setNamaBarangError(input, 'Nama barang wajib diisi.');
            return false;
        }

        if (value.length < 3) {
            setNamaBarangError(input, 'Nama barang minimal 3 karakter.');
            return false;
        }

        setNamaBarangError(input);
        return true;
    }

    function setJumlahError(input, message = '') {
        const errorEl = input.closest('div')?.querySelector('.jumlah-err');

        if (message) {
            input.classList.add('is-invalid');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
            return;
        }

        input.classList.remove('is-invalid');
        errorEl?.classList.add('hidden');
    }

    function validateJumlah(input) {
        const value = parseInt(input.value, 10);

        if (!Number.isFinite(value) || value < 1) {
            setJumlahError(input, 'Jumlah minimal 1.');
            return false;
        }

        setJumlahError(input);
        return true;
    }

    // Pasang event pada input harga item pertama yang sudah ada di HTML
    document.querySelectorAll('.input-harga-display').forEach(attachHargaEvents);

    itemsContainer.querySelectorAll('.nama-barang-input.is-invalid').forEach((input) => {
        const errorEl = input.closest('div')?.querySelector('.nama-barang-err');
        errorEl?.classList.remove('hidden');
    });

    // Delegasi untuk input jumlah (tetap number)
    itemsContainer.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-jumlah')) {
            validateJumlah(e.target);
            calculateGrandTotal();
        }

        if (e.target.classList.contains('nama-barang-input')) {
            validateNamaBarang(e.target);
        }
    });

    // Toggle filter sidebar
    if (filterToggle) {
        filterToggle.addEventListener('click', () => {
            document.getElementById('filterForm').classList.toggle('hidden');
        });
    }

    // Tambah item baru
    addItemBtn.addEventListener('click', function () {
        const newItem = document.createElement('div');
        newItem.className = 'item-row animate-main mt-4';
        newItem.innerHTML = buildItemHTML(itemIndex);
        itemsContainer.appendChild(newItem);

        // Pasang event format pada input harga baru
        attachHargaEvents(newItem.querySelector('.input-harga-display'));

        itemIndex++;
        calculateGrandTotal();
    });

    formEl?.addEventListener('submit', function(event) {
        let isValid = true;

        itemsContainer.querySelectorAll('.nama-barang-input').forEach((input) => {
            if (!validateNamaBarang(input)) {
                isValid = false;
            }
        });

        itemsContainer.querySelectorAll('.input-jumlah').forEach((input) => {
            if (!validateJumlah(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault();
            const firstError = itemsContainer.querySelector('.nama-barang-err:not(.hidden), .jumlah-err:not(.hidden)');
            firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    formEl?.addEventListener('reset', function() {
        setTimeout(() => {
            itemsContainer.querySelectorAll('.item-row').forEach((row) => {
                row.querySelectorAll('.nama-barang-input').forEach((input) => {
                    input.value = '';
                    setNamaBarangError(input);
                });

                row.querySelectorAll('.input-harga-display').forEach((input) => {
                    input.value = '';
                    input.classList.remove('is-invalid', 'border-rose-400');
                });

                row.querySelectorAll('.input-harga').forEach((input) => {
                    input.value = '';
                });

                row.querySelectorAll('.input-jumlah').forEach((input) => {
                    input.value = 1;
                    setJumlahError(input);
                });

                row.querySelectorAll('.harga-error').forEach((errorEl) => {
                    errorEl.classList.add('hidden');
                });
            });

            calculateGrandTotal();
        }, 0);
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/project/resources/views/pembelian/index.blade.php ENDPATH**/ ?>