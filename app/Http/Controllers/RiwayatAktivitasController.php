<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembelianBarang;
use App\Models\PemakaianBhp;
use App\Models\PeminjamanBarang;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RiwayatAktivitasController extends Controller
{
    public function index(Request $request)
    {
        PeminjamanBarang::expirePendingOverdue();

        $user = Auth::user();

        $userId = $user->getKey();

        $aktivitas = $this->collectPeminjaman($request, $userId, $user->user_group === 'admin')
            ->concat($this->collectPemakaian($request, $userId, $user->user_group === 'admin'))
            ->concat($this->collectPembelian($request, $userId, $user->user_group === 'admin'))
            ->sortByDesc(fn ($item) => $item['created_at']->timestamp)
            ->values();

        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $aktivitas->slice(($page - 1) * $perPage, $perPage)->values();

        $riwayat = new LengthAwarePaginator(
            $items,
            $aktivitas->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('riwayat.index', compact('riwayat'));
    }

    private function collectPeminjaman(Request $request, int $userId, bool $isAdmin): Collection
    {
        $query = PeminjamanBarang::with(['barang', 'user']);

        if (! $isAdmin) {
            $query->where('user_id', $userId);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('modul') && $request->modul !== 'peminjaman') {
            return collect();
        }

        if ($request->filled('status')) {
            $query->where('status_pinjam', $request->status);
        }

        return $query->get()->map(function ($item) {
            return [
                'modul' => 'peminjaman',
                'modul_label' => 'Peminjaman',
                'modul_color' => 'bg-blue-50 text-blue-600 border-blue-200',
                'kode' => $item->kode_barang,
                'judul' => $item->barang->nama_barang ?? 'Barang Tidak Ditemukan',
                'status' => $item->status_pinjam,
                'status_label' => match ($item->status_pinjam) {
                    'disetujui' => 'Dipinjam',
                    'kembali' => 'Sudah Dikembalikan',
                    'expired' => 'Expired',
                    default => ucfirst($item->status_pinjam),
                },
                'status_class' => match ($item->status_pinjam) {
                    'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                    'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    'kembali' => 'bg-blue-50 text-blue-600 border-blue-200',
                    'expired' => 'bg-rose-50 text-rose-600 border-rose-200',
                    'ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                    default => 'bg-slate-50 text-slate-600 border-slate-200',
                },
                'user' => $item->user->name ?? 'User Tidak Ada',
                'tanggal' => $item->tanggal_pinjam,
                'tanggal_label' => 'Tanggal Pinjam',
                'jumlah' => $item->jumlah_pinjam . ' ' . ($item->barang->satuan ?? 'Unit'),
                'detail' => $item->alasan_peminjaman,
                'created_at' => $item->created_at,
            ];
        });
    }

    private function collectPemakaian(Request $request, int $userId, bool $isAdmin): Collection
    {
        $query = PemakaianBhp::with(['barang', 'user']);

        if (! $isAdmin) {
            $query->where('user_id', $userId);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('modul') && $request->modul !== 'pemakaian') {
            return collect();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->get()->map(function ($item) {
            return [
                'modul' => 'pemakaian',
                'modul_label' => 'Pemakaian',
                'modul_color' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'kode' => $item->kode_barang,
                'judul' => $item->barang->nama_barang ?? 'Barang Tidak Ditemukan',
                'status' => $item->status,
                'status_label' => ucfirst($item->status),
                'status_class' => match ($item->status) {
                    'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                    'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    'ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                    default => 'bg-slate-50 text-slate-600 border-slate-200',
                },
                'user' => $item->user->name ?? 'User Tidak Ada',
                'tanggal' => $item->tanggal_pemakaian,
                'tanggal_label' => 'Tanggal Pemakaian',
                'jumlah' => $item->jumlah . ' ' . ($item->barang->satuan ?? 'Unit'),
                'detail' => $item->alasan_kebutuhan,
                'created_at' => $item->created_at,
            ];
        });
    }

    private function collectPembelian(Request $request, int $userId, bool $isAdmin): Collection
    {
        $query = PengajuanPembelianBarang::with('user');

        if (! $isAdmin) {
            $query->where('user_id', $userId);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('modul') && $request->modul !== 'pembelian') {
            return collect();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->get()->map(function ($item) {
            return [
                'modul' => 'pembelian',
                'modul_label' => 'Pembelian',
                'modul_color' => 'bg-violet-50 text-violet-600 border-violet-200',
                'kode' => strtoupper($item->kategori_barang),
                'judul' => $item->nama_barang,
                'status' => $item->status,
                'status_label' => ucfirst($item->status),
                'status_class' => match ($item->status) {
                    'pending' => 'bg-amber-50 text-amber-600 border-amber-200',
                    'disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                    'ditolak' => 'bg-rose-50 text-rose-600 border-rose-200',
                    default => 'bg-slate-50 text-slate-600 border-slate-200',
                },
                'user' => $item->user->name ?? 'User Tidak Ada',
                'tanggal' => $item->tanggal_pengajuan,
                'tanggal_label' => 'Tanggal Pengajuan',
                'jumlah' => $item->jumlah . ' Unit',
                'detail' => $item->alasan,
                'created_at' => $item->created_at,
            ];
        });
    }
}

