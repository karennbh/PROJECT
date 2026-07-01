<?php

namespace App\Http\Controllers;

use App\Models\BarangKantor;
use App\Models\PeminjamanBarang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanBarangController extends Controller
{
    public function index(Request $request)
    {
        PeminjamanBarang::expirePendingOverdue();

        $barangs = BarangKantor::query()
            ->select([
                'kode_barang',
                'nama_barang',
                'kategori_barang',
                'jenis_aset',
                'jenis_bhp',
                'stok',
                'status_barang',
                'status_pinjam',
                'status_penggunaan',
                'tanggal_diterima',
            ])
            ->borrowableForPeminjaman()
            ->where('status_barang', 'Aktif')
            ->where('stok', '>', 0)
            ->where(function ($query) {
                $query->where(function ($query): void {
                    $query->where('kategori_barang', 'bhp')
                        ->where('jenis_bhp', BarangKantor::JENIS_BHP_INVENTARIS_KANTOR)
                        ->whereNotIn('status_pinjam', [
                            BarangKantor::STATUS_PINJAM_DIPINJAM,
                            BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN,
                            BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN,
                        ]);
                })
                    ->orWhere(function ($query): void {
                        $query->where('status_pinjam', BarangKantor::STATUS_PINJAM_TERSEDIA)
                            ->whereIn('status_penggunaan', [
                                BarangKantor::STATUS_SIAP_DIGUNAKAN,
                            ])
                            ->whereNotNull('tanggal_diterima');
                    });
            })
            ->orderByRaw("FIELD(kategori_barang, 'aset', 'bhp')")
            ->orderBy('nama_barang')
            ->get();

        $query = PeminjamanBarang::with(['barang', 'user']);

        if (Auth::user()->user_group !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('status')) {
            $query->where('status_pinjam', $request->status);
        }

        $riwayat = $query->latest()->limit(5)->get();
        $notifikasiList = [];

        $pinjamanAktif = PeminjamanBarang::with('barang')
            ->where('user_id', Auth::id())
            ->where('status_pinjam', PeminjamanBarang::STATUS_DISETUJUI)
            ->get();

        foreach ($pinjamanAktif as $pinjam) {
            $hariIni = Carbon::today();
            $tanggalPinjam = Carbon::parse($pinjam->tanggal_pinjam);
            $tanggalKembali = Carbon::parse($pinjam->tanggal_pengembalian);

            $selisih = $hariIni->diffInDays($tanggalKembali, false);
            $namaBarang = $pinjam->barang?->nama_barang ?? $pinjam->kode_barang;

            if ($tanggalPinjam->isToday() && $tanggalKembali->isToday()) {
                $notifikasiList[] = "Barang {$namaBarang} harus dikembalikan hari ini sebelum jam operasional kantor selesai.";
            } elseif ($selisih === 1) {
                $notifikasiList[] = "Pengingat: Besok Anda harus mengembalikan {$namaBarang}.";
            } elseif ($selisih === 0) {
                $notifikasiList[] = "Hari ini adalah batas pengembalian {$namaBarang}.";
            } elseif ($selisih < 0) {
                $notifikasiList[] = "Anda terlambat mengembalikan {$namaBarang}. Segera lakukan pengembalian.";
            }
        }

        $notifikasi = $notifikasiList === [] ? null : implode(' ', $notifikasiList);

        return view('peminjaman-barang.index', compact(
            'barangs',
            'riwayat',
            'notifikasi'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_pengembalian' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'alasan_peminjaman' => ['required', 'string'],
            'bukti_peminjaman' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kategori_peminjaman' => ['required', 'in:aset,inventaris_kantor'],
            'items.*.kode_barang' => ['required', 'exists:barang_kantors,kode_barang'],
            'items.*.jumlah_pinjam' => ['required', 'integer', 'min:1'],
        ], [
            'tanggal_pinjam.required' => 'Tanggal pinjam wajib diisi.',
            'tanggal_pinjam.date' => 'Tanggal pinjam harus berupa tanggal yang valid.',
            'tanggal_pengembalian.required' => 'Tanggal pengembalian wajib diisi.',
            'tanggal_pengembalian.date' => 'Tanggal pengembalian harus berupa tanggal yang valid.',
            'tanggal_pengembalian.after_or_equal' => 'Rencana pengembalian tidak boleh lebih awal dari tanggal mulai pinjam.',
            'alasan_peminjaman.required' => 'Alasan peminjaman wajib diisi.',
            'bukti_peminjaman.required' => 'Bukti peminjaman wajib diupload.',
            'bukti_peminjaman.image' => 'Bukti peminjaman harus berupa gambar.',
            'bukti_peminjaman.mimes' => 'Bukti peminjaman harus berformat jpg, jpeg, png, atau webp.',
            'items.required' => 'Daftar barang wajib diisi.',
            'items.min' => 'Minimal ada 1 barang yang dipinjam.',
            'items.*.kategori_peminjaman.required' => 'Kategori peminjaman wajib dipilih.',
            'items.*.kategori_peminjaman.in' => 'Kategori peminjaman tidak valid.',
            'items.*.kode_barang.required' => 'Barang wajib dipilih.',
            'items.*.kode_barang.exists' => 'Barang tidak ditemukan.',
            'items.*.jumlah_pinjam.required' => 'Jumlah pinjam wajib diisi.',
            'items.*.jumlah_pinjam.integer' => 'Jumlah pinjam harus berupa angka bulat.',
            'items.*.jumlah_pinjam.min' => 'Jumlah pinjam harus lebih dari 0.',
        ]);

        DB::beginTransaction();

        try {
            $buktiPeminjaman = $request->file('bukti_peminjaman')->store('bukti_peminjaman', 'public');

            $requestedItems = collect($validated['items'])
                ->groupBy('kode_barang')
                ->map(fn ($items) => [
                    'jumlah_pinjam' => $items->sum(fn ($item) => (int) $item['jumlah_pinjam']),
                    'kategori_peminjaman' => $items->first()['kategori_peminjaman'] ?? null,
                ]);

            $barangs = BarangKantor::query()
                ->whereIn('kode_barang', $requestedItems->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('kode_barang');

            foreach ($requestedItems as $kodeBarang => $item) {
                $barang = $barangs->get($kodeBarang);

                if (! $barang || ! $barang->isAvailableToBorrow((int) $item['jumlah_pinjam'])) {
                    $stok = (int) ($barang?->stok ?? 0);
                    $namaBarang = $barang?->nama_barang ?? $kodeBarang;

                    throw ValidationException::withMessages([
                        'items' => "Jumlah peminjaman {$namaBarang} tidak boleh melebihi stok tersedia ({$stok}).",
                    ]);
                }

                $kategoriPeminjaman = $barang->kategori_barang === 'aset'
                    ? 'aset'
                    : BarangKantor::JENIS_BHP_INVENTARIS_KANTOR;

                if (($item['kategori_peminjaman'] ?? null) !== $kategoriPeminjaman) {
                    throw new \RuntimeException("Kategori barang untuk {$barang->nama_barang} tidak sesuai dengan pilihan form.");
                }

                PeminjamanBarang::create([
                    'user_id' => Auth::id(),
                    'kode_barang' => $kodeBarang,
                    'kategori_barang' => $barang->kategori_barang,
                    'tanggal_pinjam' => $validated['tanggal_pinjam'],
                    'tanggal_pengembalian' => $validated['tanggal_pengembalian'],
                    'jumlah_pinjam' => (int) $item['jumlah_pinjam'],
                    'alasan_peminjaman' => $validated['alasan_peminjaman'],
                    'bukti_peminjaman' => $buktiPeminjaman,
                    'status_pinjam' => PeminjamanBarang::STATUS_PENDING,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Pengajuan peminjaman barang kantor berhasil dikirim');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function approve($id)
    {
        return DB::transaction(function () use ($id) {
            $pinjam = PeminjamanBarang::lockForUpdate()->findOrFail($id);

            if ($pinjam->status_pinjam !== PeminjamanBarang::STATUS_PENDING) {
                return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
            }

            if (Carbon::today()->gt(Carbon::parse($pinjam->tanggal_pengembalian)->startOfDay())) {
                $pinjam->update(['status_pinjam' => PeminjamanBarang::STATUS_EXPIRED]);

                return back()->with('error', 'Pengajuan peminjaman barang kantor sudah expired karena melewati tanggal pengembalian.');
            }

            $barang = BarangKantor::where('kode_barang', $pinjam->kode_barang)
                ->lockForUpdate()
                ->first();

            if (! $barang) {
                return back()->with('error', 'Data barang tidak ditemukan.');
            }

            if (! $barang->isAvailableToBorrow((int) $pinjam->jumlah_pinjam)) {
                return back()->with('error', 'Barang tidak tersedia atau stok tidak mencukupi.');
            }

            $pinjam->update(['status_pinjam' => PeminjamanBarang::STATUS_DISETUJUI]);
            $barang->markAsBorrowed((int) $pinjam->jumlah_pinjam);

            return back()->with('success', 'Peminjaman disetujui dan status barang diperbarui.');
        });
    }

    public function kembalikan($id)
    {
        DB::beginTransaction();

        try {
            request()->validate([
                'bukti_pengembalian' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ], [
                'bukti_pengembalian.required' => 'Bukti pengembalian wajib diupload.',
                'bukti_pengembalian.image' => 'Bukti pengembalian harus berupa gambar.',
                'bukti_pengembalian.mimes' => 'Bukti pengembalian harus berformat jpg, jpeg, png, atau webp.',
            ]);

            $peminjaman = PeminjamanBarang::lockForUpdate()->findOrFail($id);

            if ($peminjaman->status_pinjam !== PeminjamanBarang::STATUS_DISETUJUI) {
                throw new \RuntimeException('Hanya barang yang sedang dipinjam yang bisa dikembalikan.');
            }

            $peminjaman->update([
                'status_pinjam' => PeminjamanBarang::STATUS_MENUNGGU_VERIFIKASI_PENGEMBALIAN,
                'bukti_pengembalian' => request()->file('bukti_pengembalian')->store('bukti_pengembalian', 'public'),
            ]);

            DB::commit();

            return back()->with('success', 'Pengajuan pengembalian berhasil dikirim dan menunggu verifikasi admin.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function riwayatSemua(Request $request)
    {
        PeminjamanBarang::expirePendingOverdue();

        $query = PeminjamanBarang::with(['barang', 'user']);

        if (Auth::user()->user_group !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('status')) {
            $query->where('status_pinjam', $request->status);
        }

        $riwayat = $query->latest()->paginate(10)->withQueryString();

        return view('peminjaman-barang.riwayat', compact('riwayat'));
    }

}
