@extends('layouts.sidebar')
@section('title', 'Semua Riwayat Pembelian')
@section('page_title', 'Riwayat Pengajuan Pembelian')

@push('styles')
<style>
    @keyframes fadeInSlide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-main { animation: fadeInSlide 0.4s ease-out forwards; }
    .modern-card { background: white; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); border-radius: 16px; }
    .modern-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.95rem; color: #334155; }
    .modern-label { display: block; font-size: 0.82rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; }
    .status-badge { display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 800; line-height: 1.2; letter-spacing: 0.04em; padding: 0.45rem 0.9rem; border-radius: 999px; text-transform: uppercase; }
    .history-item { border: 1px solid #e2e8f0; border-radius: 1.25rem; background: #fff; padding: 1.25rem; transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease; }
    .history-item:hover { border-color: #bfdbfe; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); transform: translateY(-1px); }
    .history-index { width: 3.7rem; height: 3.7rem; border-radius: 0.95rem; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; flex-shrink: 0; }
    .history-title { font-size: 1rem; font-weight: 800; line-height: 1.35; color: #1e293b; }
    .history-note { font-size: 0.92rem; line-height: 1.55; color: #64748b; }
    .history-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem; font-size: 0.92rem; color: #64748b; }
    .history-meta strong { color: #334155; font-weight: 800; }
    .history-dot { color: #cbd5e1; font-weight: 900; }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-8 animate-main max-w-[1400px]">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Semua Riwayat Pembelian</h2>
            <p class="page-section-subtitle">Daftar lengkap pengajuan pembelian barang</p>
        </div>
        <a href="{{ route('pembelian.index') }}" class="text-sm font-semibold text-white bg-sky-400 px-4 py-2.5 rounded-lg shadow-lg shadow-sky-400/25 hover:bg-sky-500 transition-all"><- Kembali ke Form</a>
    </div>

    <div class="modern-card p-5">
        <h3 class="panel-title mb-3 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>Filter Riwayat</h3>
        <form method="GET" action="{{ route('pembelian.riwayat') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div><label class="modern-label">Tanggal Dari</label><input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="modern-input px-3 py-2 w-full" onchange="this.form.submit()"></div>
            <div><label class="modern-label">Tanggal Sampai</label><input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="modern-input px-3 py-2 w-full" onchange="this.form.submit()"></div>
            <div>
                <label class="modern-label">Status Pengajuan</label>
                <select name="status" class="modern-input px-3 py-2 bg-white" onchange="this.form.submit()">
                    <option value="">Semua Status Pengajuan</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="flex items-end"><a href="{{ route('pembelian.riwayat') }}" class="w-full text-center px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-200">Reset</a></div>
        </form>
    </div>

    <div class="modern-card p-5">
        <div class="mb-4 flex items-center justify-between"><h3 class="panel-title">Daftar Pembelian ({{ $riwayat->total() }} Item Ditemukan)</h3></div>
        <div class="space-y-3">
            @forelse($riwayat as $item)
                @php
                    $statusClass = match($item->status) {
                        'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                        'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        'ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                @endphp
                <div class="history-item">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                        <div class="flex gap-4 flex-1 min-w-0">
                            <div class="history-index">{{ $loop->iteration + ($riwayat->currentPage()-1) * $riwayat->perPage() }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3 mb-2">
                                    <div class="min-w-0">
                                        <span class="inline-flex bg-slate-100 text-slate-500 px-2.5 py-1 rounded-md text-xs font-black uppercase">{{ $item->kategori_barang }}</span>
                                        <h4 class="history-title mt-1">{{ $item->nama_barang }}</h4>
                                        <p class="history-note">Oleh: {{ $item->user->name }} | {{ $item->jumlah }} Unit</p>
                                    </div>
                                </div>
                                <div class="history-meta">
                                    <span><strong>PEMOHON:</strong> {{ $item->user->name }}</span>
                                    <span class="history-dot">•</span>
                                    <span><strong>TANGGAL:</strong> {{ \Carbon\Carbon::parse($item->tanggal_pengajuan)->format('d/m/Y') }}</span>
                                    <span class="history-dot">•</span>
                                    <span><strong>JUMLAH:</strong> {{ $item->jumlah }} UNIT</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left xl:text-right xl:self-center">
                            <span class="status-badge border {{ $statusClass }}">{{ $item->status }}</span>
                            <p class="text-2xl font-black text-blue-600 mt-3">Rp {{ number_format($item->sub_total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-sm">Data tidak ditemukan.</div>
            @endforelse
        </div>
        @if($riwayat->hasPages())<div class="mt-6 pt-4 border-t border-slate-100">{{ $riwayat->appends(request()->query())->links() }}</div>@endif
    </div>
</div>
@endsection
