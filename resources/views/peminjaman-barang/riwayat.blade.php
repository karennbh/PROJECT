@extends('layouts.sidebar')
@section('title', 'Semua Riwayat Peminjaman')
@section('page_title', 'Riwayat Peminjaman Barang Kantor')

@push('styles')
<style>
    @keyframes fadeInSlide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-main { animation: fadeInSlide 0.4s ease-out forwards; }
    .modern-card { background: white; border: 1px solid rgba(226, 232, 240, 0.8); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04); border-radius: 16px; }
    .modern-input { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.95rem; transition: all 0.2s; width: 100%; color: #334155; }
    .modern-input:focus { background: white; border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1); outline: none; }
    .modern-label { display: block; font-size: 0.82rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.03em; }
    .status-badge { display: inline-flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 800; line-height: 1.2; letter-spacing: 0.04em; padding: 0.45rem 0.9rem; border-radius: 999px; text-transform: uppercase; }
    .history-item { border: 1px solid #e2e8f0; border-radius: 1.25rem; background: #fff; padding: 1.25rem; transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease; }
    .history-item:hover { border-color: #bfdbfe; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); transform: translateY(-1px); }
    .history-index { width: 3.7rem; height: 3.7rem; border-radius: 0.95rem; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; flex-shrink: 0; }
    .history-code { font-size: 0.82rem; font-weight: 800; letter-spacing: 0.03em; text-transform: uppercase; color: #64748b; }
    .history-title { font-size: 1rem; font-weight: 800; line-height: 1.35; color: #1e293b; }
    .history-note { font-size: 0.92rem; line-height: 1.55; color: #64748b; }
    .history-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem; font-size: 0.92rem; color: #64748b; }
    .history-meta strong { color: #334155; font-weight: 800; }
    .history-dot { color: #cbd5e1; font-weight: 900; }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-8 animate-main max-w-[1400px]">
    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl border border-emerald-200 mb-4 text-xs font-bold animate-main"><div class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>{{ session('success') }}</div></div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 text-rose-600 p-4 rounded-xl border border-rose-200 mb-4 text-xs font-bold animate-main"><div class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>{{ session('error') }}</div></div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="page-section-title">Semua Riwayat Peminjaman</h2>
            <p class="page-section-subtitle">Daftar lengkap pengajuan peminjaman barang kantor dan status pengembalian</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('peminjaman.index') }}" class="text-sm font-semibold text-white bg-sky-400 px-4 py-2.5 rounded-lg shadow-lg shadow-sky-400/25 hover:bg-sky-500 transition-all"><- Kembali ke Form</a>
        </div>
    </div>

    <div class="modern-card p-5">
        <h3 class="panel-title mb-3 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>Filter Riwayat</h3>
        <form id="filterForm" method="GET" action="{{ route('peminjaman.riwayat') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div><label class="modern-label">Tanggal Dari</label><input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="modern-input px-3 py-2" onchange="this.form.submit()"></div>
            <div><label class="modern-label">Tanggal Sampai</label><input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="modern-input px-3 py-2" onchange="this.form.submit()"></div>
            <div>
                <label class="modern-label">Status Pengajuan</label>
                <select name="status" class="modern-input px-3 py-2 bg-white cursor-pointer" onchange="this.form.submit()">
                    <option value="">Semua Status Pengajuan</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="menunggu_verifikasi_pengembalian" {{ request('status') == 'menunggu_verifikasi_pengembalian' ? 'selected' : '' }}>Menunggu Verifikasi Admin</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="kembali" {{ request('status') == 'kembali' ? 'selected' : '' }}>Sudah Kembali</option>
                </select>
            </div>
            <div class="flex items-end gap-2"><a href="{{ route('peminjaman.riwayat') }}" class="w-full text-center px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-200 transition-all">Reset Filter</a></div>
        </form>
    </div>

    <div class="modern-card p-5">
        <div class="mb-4 flex items-center justify-between"><h3 class="panel-title">Daftar Peminjaman ({{ $riwayat->total() }} Item Ditemukan)</h3></div>
        <div class="space-y-3">
            @forelse($riwayat as $item)
                @php
                    $statusClass = match($item->status_pinjam) {
                        'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                        'expired' => 'bg-rose-50 text-rose-600 border-rose-200',
                        'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                        'ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                        'kembali' => 'bg-blue-50 text-blue-600 border-blue-200',
                        'menunggu_verifikasi_pengembalian' => 'bg-blue-50 text-blue-600 border-blue-200',
                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                    };
                    $timelineLabel = null;
                    $timelineClass = null;
                    if ($item->status_pinjam === 'disetujui') {
                        $hariIni = \Carbon\Carbon::today();
                        $tglKembali = \Carbon\Carbon::parse($item->tanggal_pengembalian);
                        $selisih = $hariIni->diffInDays($tglKembali, false);
                        if ($selisih === 1) { $timelineLabel = 'H-1 PENGEMBALIAN'; $timelineClass = 'bg-yellow-100 text-yellow-700 border-yellow-200'; }
                        elseif ($selisih === 0) { $timelineLabel = 'PENGEMBALIAN HARI INI'; $timelineClass = 'bg-orange-100 text-orange-700 border-orange-200'; }
                        elseif ($selisih < 0) { $timelineLabel = 'TERLAMBAT'; $timelineClass = 'bg-red-100 text-red-700 border-red-200'; }
                    }
                @endphp
                <div class="history-item animate-main">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-4">
                                <div class="history-index">{{ $loop->iteration + ($riwayat->currentPage()-1) * $riwayat->perPage() }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3 mb-2">
                                        <div class="min-w-0">
                                            <p class="history-code">{{ $item->kode_barang }}</p>
                                            <h4 class="history-title">{{ $item->barang->nama_barang ?? 'Barang Tidak Ditemukan' }}</h4>
                                            <p class="text-xs font-bold text-blue-500 uppercase tracking-widest">
                                                {{ $item->barang?->jenis_barang_label ?? 'Kategori Tidak Ditemukan' }}
                                            </p>
                                            <p class="history-note italic">"{{ $item->alasan_peminjaman }}"</p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                                            <span class="status-badge border {{ $statusClass }}">{{ match($item->status_pinjam) { 'kembali' => 'SUDAH DIKEMBALIKAN', 'expired' => 'EXPIRED', 'menunggu_verifikasi_pengembalian' => 'MENUNGGU VERIFIKASI ADMIN', default => $item->status_pinjam, } }}</span>
                                            @if($item->status_pinjam === 'disetujui' && ! $item->tanggal_dikembalikan)
                                                <span class="status-badge border bg-slate-100 text-slate-500 border-slate-200">MASIH DIPINJAM</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="history-meta">
                                        <span><strong>{{ $item->user->name ?? 'USER N/A' }}</strong></span>
                                        <span class="history-dot">•</span>
                                        <span><strong>PINJAM:</strong> {{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d/m/Y') }}</span>
                                        <span class="history-dot">•</span>
                                        <span><strong>ESTIMASI KEMBALI:</strong> {{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') }}</span>
                                        <span class="history-dot">•</span>
                                        <span><strong>JUMLAH:</strong> {{ $item->jumlah_pinjam }} UNIT</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($timelineLabel)
                            <div class="xl:self-center"><span class="status-badge border {{ $timelineClass }}">{{ $timelineLabel }}</span></div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400"><svg class="w-16 h-16 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg><p class="text-sm font-semibold">Tidak ada riwayat peminjaman</p><p class="text-xs mt-1">Coba sesuaikan filter atau lakukan pengajuan baru.</p></div>
            @endforelse
        </div>
        @if($riwayat->hasPages())<div class="mt-6 pt-4 border-t border-slate-100">{{ $riwayat->appends(request()->query())->links() }}</div>@endif
    </div>
</div>
@endsection
