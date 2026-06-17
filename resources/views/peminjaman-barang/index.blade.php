@extends('layouts.sidebar')

@section('title', 'Peminjaman Barang Kantor')
@section('page_title', 'Form Peminjaman Barang Kantor')

@push('styles')
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
    
    .modern-input:focus, select:focus {
        background: white;
        border-color: #60a5fa;
        outline: none;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
    }

    .modern-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .item-row {
        background: #f8fafc;
        border: 1.5px solid #edf2f7;
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        position: relative;
    }

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
        border: none;
        z-index: 10;
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
@endpush

@section('content')
<div class="space-y-6 pb-10 animate-main max-w-[1400px]">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Pengajuan Peminjaman</h2>
            <p class="page-section-subtitle">Formulir peminjaman barang inventaris kantor</p>
        </div>
        <div class="flex items-center">
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-500 bg-white px-4 py-2.5 rounded-full border border-slate-200 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-xs font-bold">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-xs font-bold">
        {{ session('error') }}
    </div>
    @endif
    {{-- NOTIFIKASI PENGEMBALIAN --}}
@if(isset($notifikasi) && $notifikasi)
<div class="bg-yellow-50 border border-yellow-300 text-yellow-900 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 mb-4">
    
    <svg xmlns="http://www.w3.org/2000/svg" 
         class="h-4 w-4 text-yellow-700" 
         fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
    </svg>

    {{ $notifikasi }}

</div>
@endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="modern-card p-6">
                <form action="{{ route('peminjaman.store') }}" method="POST" enctype="multipart/form-data" id="peminjamanForm" class="space-y-5" novalidate>
                    @csrf
                    
                    <div>
                        <label class="modern-label">Nama Pengaju</label>
                        <input type="text" class="modern-input px-3 py-2.5 bg-slate-100 cursor-not-allowed" value="{{ Auth::user()->name }} ({{ Auth::user()->user_group }})" readonly>
                        <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="modern-label">Tanggal Pinjam</label>
                            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" class="modern-input px-3 py-2.5" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="modern-label">Tanggal Pengembalian</label>
                            <input type="date" name="tanggal_pengembalian" id="tanggal_pengembalian" class="modern-input px-3 py-2.5" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="border-t pt-5">
                        <div class="flex justify-between items-center mb-4">
                            <label class="modern-label !mb-0">Barang Kantor yang Dipinjam</label>
                            <button type="button" id="addItemBtn" class="text-xs font-bold text-blue-600 hover:underline">
                                + Tambah Item
                            </button>
                        </div>

                        <div id="items-container">
                            <div class="item-row">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Kategori Barang</label>
                                        <select name="items[0][kategori_peminjaman]" class="kategori-peminjaman-select modern-input px-3 py-2.5 bg-white" required>
                                            <option value="aset">Aset Tetap</option>
                                            <option value="inventaris_kantor" selected>BPP Inventaris Kantor</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Nama Barang</label>
                                        <select name="items[0][kode_barang]" class="barang-select barang-select-native modern-input px-3 py-2.5 bg-white">
                                            <option value="" disabled {{ old('items.0.kode_barang', request('kode_barang')) ? '' : 'selected' }}>-- Pilih Barang --</option>
                                            @foreach($barangs as $barang)
                                                <option value="{{ $barang->kode_barang }}" data-stok="{{ $barang->stok }}" {{ old('items.0.kode_barang', request('kode_barang')) == $barang->kode_barang ? 'selected' : '' }}>
                                                    {{ $barang->kode_barang }} - {{ $barang->nama_barang }} ({{ $barang->jenis_barang_label }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="barang-err field-error {{ $errors->has('items.0.kode_barang') ? '' : 'hidden' }}">{{ $errors->first('items.0.kode_barang') ?: 'Nama barang wajib dipilih.' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Jumlah</label>
                                        <input type="number" name="items[0][jumlah_pinjam]" class="jumlah-input modern-input px-3 py-2.5" value="1" min="1" required>
                                        <p class="stok-err field-error hidden">Jumlah minimal 1.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="modern-label">Alasan</label>
                        <textarea name="alasan_peminjaman" rows="3" class="modern-textarea px-3 py-2.5" placeholder="Tujuan peminjaman..." required></textarea>
                    </div>

                    <div>
                        <label class="modern-label">Bukti Pendukung</label>
                        <input type="file" name="bukti_peminjaman" required class="modern-input px-3 py-2.5 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-400 mt-1.5">Format: JPG, JPEG, PNG, WEBP. Ukuran maksimal: 2MB.</p>
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

        {{-- Sidebar Riwayat --}}
        <div class="space-y-6">
            <div class="modern-card p-5">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xs font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-history text-slate-400"></i>
                        Riwayat Pengajuan <span class="text-sm text-slate-400 font-medium">({{ $riwayat->count() }})</span>
                    </h3>
                    <button type="button" id="filterToggle" class="p-2 bg-slate-50 rounded-lg text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition-all">
                        <i class="fas fa-filter text-xs"></i>
                    </button>
                </div>

                <div id="filterForm" class="mb-6 p-4 bg-blue-50/50 rounded-2xl border border-blue-100 {{ request()->anyFilled(['tanggal_dari', 'tanggal_sampai', 'status']) ? '' : 'hidden' }}">
                    <form method="GET" action="{{ route('peminjaman.index') }}" class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="modern-input text-sm py-2.5 px-3">
                            <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="modern-input text-sm py-2.5 px-3">
                        </div>
                        <select name="status" class="modern-input text-sm py-2.5 px-3 bg-white">
                            <option value="">Semua Status Pengajuan</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="menunggu_verifikasi_pengembalian" {{ request('status') == 'menunggu_verifikasi_pengembalian' ? 'selected' : '' }}>Menunggu Verifikasi Admin</option>
                            <option value="kembali" {{ request('status') == 'kembali' ? 'selected' : '' }}>Kembali</option>
                        </select>
                        <div class="flex gap-2 pt-1">
                            <button type="submit" class="flex-1 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold">Cari</button>
                            <a href="{{ route('peminjaman.index') }}" class="px-3 py-2.5 bg-white text-slate-400 rounded-lg text-sm border border-slate-200">Reset</a>
                        </div>
                    </form>
                </div>
                
                <div class="space-y-4">
                    @forelse($riwayat as $item)
                    <div class="p-4 rounded-2xl border border-slate-50 bg-slate-50/30 hover:bg-white hover:border-blue-100 hover:shadow-md transition-all group">
                        <div class="flex justify-between items-start mb-2">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">#{{ $item->kode_barang }}</p>
                                <h4 class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                    {{ $item->barang->nama_barang ?? 'Barang Terhapus' }}
                                </h4>
                                <p class="text-xs font-bold text-blue-500 uppercase tracking-widest">
                                    {{ $item->barang?->jenis_barang_label ?? 'Kategori Tidak Ditemukan' }}
                                </p>
                                <p class="text-xs font-bold text-slate-700">x{{ $item->jumlah_pinjam }} Unit</p>
                            </div>
                            @php
                                $statusColor = match($item->status_pinjam) {
                                    'pending' => 'bg-amber-100 text-amber-600',
                                    'expired' => 'bg-rose-100 text-rose-600',
                                    'disetujui' => 'bg-emerald-100 text-emerald-600',
                                    'menunggu_verifikasi_pengembalian' => 'bg-blue-100 text-blue-600',
                                    'kembali' => 'bg-blue-100 text-blue-600',
                                    'ditolak' => 'bg-rose-100 text-rose-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="status-badge {{ $statusColor }}">
                                {{ match($item->status_pinjam) {
                                    'menunggu_verifikasi_pengembalian' => 'menunggu verifikasi admin',
                                    'expired' => 'expired',
                                    default => $item->status_pinjam,
                                } }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-end text-xs text-slate-500 pt-3 border-t border-slate-100/50">
                            <span class="flex items-center gap-1"><i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/y') }}</span>
                        </div>
                        @if($item->status_pinjam == 'disetujui')
                        <form action="{{ route('peminjaman.kembalikan', $item->id_peminjaman) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-2">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Bukti Pengembalian</label>
                                <input type="file" name="bukti_pengembalian" required class="modern-input px-3 py-2.5 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-white border border-slate-200 text-sm font-bold text-slate-600 rounded-xl hover:bg-slate-900 hover:text-white transition-all">
                                Kembalikan Barang
                            </button>
                        </form>
                        @endif
                        @if($item->status_pinjam == 'menunggu_verifikasi_pengembalian')
                        <div class="status-badge mt-3 w-full bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-center">
                            MENUNGGU VERIFIKASI ADMIN
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-10">
                        <p class="text-sm font-medium text-slate-400">Belum ada riwayat peminjaman.</p>
                    </div>
                    @endforelse
                </div>

                @if($riwayat->count() > 0)
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <a href="{{ route('peminjaman.riwayat') }}" class="block text-center py-3 bg-sky-400 hover:bg-sky-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-sky-400/25 transition-all">
                        Buka Semua Riwayat
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;
    const barangOptions = `{!! $barangs->map(fn($b) => '<option value="' . e($b->kode_barang) . '" data-stok="' . e($b->stok) . '">' . e($b->kode_barang . ' - ' . $b->nama_barang . ' (' . $b->jenis_barang_label . ')') . '</option>')->implode('') !!}`;

    const formEl = document.getElementById('peminjamanForm');
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
                <button type="button" class="remove-item-btn" onclick="this.closest('.item-row').remove()">✕</button>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Kategori Barang</label>
                        <select name="items[${itemIndex}][kategori_peminjaman]" class="kategori-peminjaman-select modern-input px-3 py-2.5 bg-white" required>
                            <option value="aset">Aset Tetap</option>
                            <option value="inventaris_kantor" selected>BPP Inventaris Kantor</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Nama Barang</label>
                        <select name="items[${itemIndex}][kode_barang]" class="barang-select barang-select-native modern-input px-3 py-2.5 bg-white" required>
                            <option value="" disabled selected>-- Pilih Barang --</option>
                            ${barangOptions}
                        </select>
                        <p class="barang-err field-error hidden">Nama barang wajib dipilih.</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-400 mb-1 block uppercase">Jumlah</label>
                        <input type="number" name="items[${itemIndex}][jumlah_pinjam]" class="jumlah-input modern-input px-3 py-2.5" value="1" min="1" required>
                        <p class="stok-err field-error hidden">Jumlah minimal 1.</p>
                    </div>
                </div>`;
            itemsContainer.appendChild(newItem);
            bindStokValidation(newItem);
            itemIndex++;
        });
    }

    function bindStokValidation(row) {
        const selectEl = row.querySelector('.barang-select');
        const jumlahEl = row.querySelector('.jumlah-input');
        const errEl = row.querySelector('.stok-err');

        function validate() {
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const stok = selectedOption ? parseInt(selectedOption.getAttribute('data-stok')) : 0;
            const jumlah = parseInt(jumlahEl.value) || 0;

            if (stok > 0) {
                jumlahEl.max = stok;
            } else {
                jumlahEl.removeAttribute('max');
            }

            if (jumlah < 1) {
                errEl.textContent = 'Jumlah minimal 1.';
                errEl.classList.remove('hidden');
                jumlahEl.classList.add('is-invalid');
                return false;
            }

            if (selectEl.value && jumlah > stok) {
                errEl.textContent = `Stok tersedia hanya ${stok}.`;
                errEl.classList.remove('hidden');
                jumlahEl.classList.add('is-invalid');
                return false;
            } else {
                errEl.classList.add('hidden');
                jumlahEl.classList.remove('is-invalid');
                return true;
            }
        }
        selectEl.addEventListener('change', validate);
        jumlahEl.addEventListener('input', validate);
        validate();
    }

    const initialRow = document.querySelector('.item-row');
    if (initialRow) bindStokValidation(initialRow);

    function validateAllStok() {
        const totals = {};
        let isValid = true;

        document.querySelectorAll('#items-container .item-row').forEach((row) => {
            const selectEl = row.querySelector('.barang-select');
            const jumlahEl = row.querySelector('.jumlah-input');
            const errEl = row.querySelector('.stok-err');
            const selectedOption = selectEl?.options[selectEl.selectedIndex];
            const kodeBarang = selectEl?.value;
            const stok = selectedOption ? parseInt(selectedOption.getAttribute('data-stok')) || 0 : 0;
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

            if (!kodeBarang) {
                return;
            }

            totals[kodeBarang] ??= { stok, jumlah: 0, rows: [] };
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

    formEl?.addEventListener('submit', function(event) {
        // Validasi: setiap baris wajib memilih barang
        let barangValid = true;
        document.querySelectorAll('#items-container .item-row').forEach((row) => {
            const selectEl = row.querySelector('.barang-select');
            const errEl    = row.querySelector('.barang-err');
            const trigger  = row.querySelector('.searchable-select-trigger');
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
            const firstErr = document.querySelector('.barang-err:not(.hidden), .stok-err:not(.hidden)');
            firstErr?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const barangItems = @js(
        $barangs->map(function ($b) {
            return [
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'kategori_barang' => $b->kategori_barang,
                'jenis_aset' => $b->jenis_aset,
                'kategori_peminjaman' => $b->kategori_barang === 'aset' ? 'aset' : 'inventaris_kantor',
                'jenis_barang_label' => $b->jenis_barang_label,
                'stok' => (int) $b->stok,
            ];
        })->values()
    );

    function getItemLabel(item) {
        return `${item.kode_barang} - ${item.nama_barang} (${item.jenis_barang_label})`;
    }

    function getRowKategori(row) {
        return row.querySelector('.kategori-peminjaman-select')?.value || 'inventaris_kantor';
    }

    function getItemsForRow(row) {
        const kategori = getRowKategori(row);

        return barangItems.filter((item) => item.kategori_peminjaman === kategori);
    }

    function syncSelectOptions(selectEl, items, selectedValue = '') {
        const placeholderSelected = selectedValue ? '' : 'selected';
        const options = items.map((item) => `
            <option value="${item.kode_barang}" data-stok="${item.stok}" ${selectedValue === item.kode_barang ? 'selected' : ''}>
                ${getItemLabel(item)}
            </option>
        `).join('');

        selectEl.innerHTML = `<option value="" disabled ${placeholderSelected}>-- Pilih Barang --</option>${options}`;
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
        const kategoriEl = row.querySelector('.kategori-peminjaman-select');

        if (!selectEl) {
            return;
        }

        if (!selectEl.classList.contains('barang-select')) {
            selectEl.classList.add('barang-select');
        }

        if (selectEl.dataset.searchEnhanced === 'true') {
            return;
        }

        const initialValue = selectEl.value || '';
        const initialItem = barangItems.find((item) => item.kode_barang === initialValue);

        if (kategoriEl && initialItem) {
            kategoriEl.value = initialItem.kategori_peminjaman;
        }

        syncSelectOptions(selectEl, getItemsForRow(row), initialValue);

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
            const rowItems = getItemsForRow(row);

            if (normalizedKeyword === '') {
                return rowItems;
            }

            return rowItems.filter((item) =>
                item.kode_barang.toLowerCase().includes(normalizedKeyword) ||
                item.nama_barang.toLowerCase().includes(normalizedKeyword)
            );
        }

        function openDropdown() {
            closeAllDropdowns(dropdown);
            dropdown.classList.add('open');
            panelEl.hidden = false;
            searchEl.value = '';
            renderDropdownOptions(getItemsForRow(row));
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
            syncSelectOptions(selectEl, getItemsForRow(row), selectedValue);
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
        kategoriEl?.addEventListener('change', function() {
            selectEl.value = '';
            syncSelectOptions(selectEl, getItemsForRow(row));
            updateTriggerLabel();
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        });
        updateTriggerLabel();

        if (errorEl && !errorEl.classList.contains('hidden')) {
            triggerEl.classList.add('is-invalid');
        }

        selectEl.dataset.searchEnhanced = 'true';
    }

    document.querySelectorAll('#items-container .item-row').forEach(enhanceRow);

    document.getElementById('peminjamanForm')?.addEventListener('reset', function() {
        setTimeout(() => {
            document.querySelectorAll('#items-container .item-row').forEach((row) => {
                const kategoriEl = row.querySelector('.kategori-peminjaman-select');
                const selectEl = row.querySelector('.barang-select');
                const jumlahEl = row.querySelector('.jumlah-input');
                const errEl = row.querySelector('.barang-err');
                const stokErrEl = row.querySelector('.stok-err');
                const trigger = row.querySelector('.searchable-select-trigger');

                if (kategoriEl) {
                    kategoriEl.value = 'inventaris_kantor';
                }

                if (selectEl) {
                    selectEl.value = '';
                    syncSelectOptions(selectEl, getItemsForRow(row));
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (jumlahEl) {
                    jumlahEl.value = 1;
                    jumlahEl.style.borderColor = '';
                    jumlahEl.removeAttribute('max');
                }

                errEl?.classList.add('hidden');
                stokErrEl?.classList.add('hidden');
                trigger?.classList.remove('is-invalid');
            });
        }, 0);
    });

    const tanggalPinjam = document.getElementById('tanggal_pinjam');
    const tanggalPengembalian = document.getElementById('tanggal_pengembalian');

    if (tanggalPinjam && tanggalPengembalian) {
        const syncTanggalPengembalian = () => {
            tanggalPengembalian.min = tanggalPinjam.value || tanggalPinjam.min;

            if (tanggalPengembalian.value < tanggalPengembalian.min) {
                tanggalPengembalian.value = tanggalPengembalian.min;
            }
        };

        tanggalPinjam.addEventListener('change', syncTanggalPengembalian);
        syncTanggalPengembalian();
    }

    const itemsContainer = document.getElementById('items-container');
    if (itemsContainer) {
        const observer = new MutationObserver(() => {
            itemsContainer.querySelectorAll('.item-row').forEach(enhanceRow);
        });

        observer.observe(itemsContainer, { childList: true });
    }
});
</script>
@endsection
