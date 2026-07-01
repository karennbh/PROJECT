<?php

namespace App\Http\Controllers;

use App\Models\PemakaianBHP; 
use App\Models\BarangKantor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PemakaianBHPController extends Controller
{
    public function index(Request $request)
    {
        $barangs = BarangKantor::query()
            ->select(['kode_barang', 'nama_barang', 'stok', 'satuan', 'kategori_barang', 'jenis_bhp', 'status_barang'])
            ->where('kategori_barang', 'bhp')
            ->where('jenis_bhp', BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR)
            ->where('status_barang', BarangKantor::STATUS_AKTIF)
            ->where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        $query = PemakaianBHP::with(['barang', 'user']);

        // FILTER: Hanya tampilkan milik user yang login (Kecuali Admin)
        if (Auth::user()->user_group !== 'admin') {
            $query->where('user_id', Auth::id());
        }
        
        // Filter Pencarian Tanggal & Status
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Ambil 4 data TERBARU milik user tersebut
        $riwayat = $query->orderBy('id_pemakaian', 'desc')->take(4)->get();

        return view('pemakaian.index', compact('barangs', 'riwayat'));
    }

    public function riwayatSemua(Request $request)
    {
        $query = PemakaianBHP::with(['barang', 'user']);
        
        // FILTER: User hanya melihat riwayatnya sendiri
        if (Auth::user()->user_group !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pemakaian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pemakaian', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $riwayat = $query->orderBy('id_pemakaian', 'desc')->paginate(20);

        return view('pemakaian.riwayat', compact('riwayat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alasan_kebutuhan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.kode_barang' => 'required|exists:barang_kantors,kode_barang',
            'items.*.jumlah' => 'required|integer|min:1',
            'bukti_pendukung' => 'nullable|image|mimes:jpg,png|max:2048',
        ], [
            'alasan_kebutuhan.required' => 'Alasan kebutuhan wajib diisi.',
            'items.required' => 'Daftar barang wajib diisi.',
            'items.min' => 'Minimal ada 1 barang yang diajukan.',
            'items.*.kode_barang.required' => 'Barang wajib dipilih.',
            'items.*.kode_barang.exists' => 'Barang tidak ditemukan.',
            'items.*.jumlah.min' => 'Jumlah pemakaian minimal adalah 1.',
            'items.*.jumlah.required' => 'Jumlah pemakaian harus diisi.',
            'items.*.jumlah.integer' => 'Jumlah pemakaian harus berupa angka bulat.',
            'bukti_pendukung.image' => 'Bukti pendukung harus berupa gambar.',
            'bukti_pendukung.mimes' => 'Bukti pendukung harus berformat jpg atau png.',
        ]);

        try {
            DB::beginTransaction();
            $tanggalPemakaian = today()->toDateString();
            $buktiPath = $request->hasFile('bukti_pendukung')
                ? $request->file('bukti_pendukung')->store('bukti_pemakaian_bhp', 'public')
                : null;
            
            $requestedItems = collect($request->items)
                ->groupBy('kode_barang')
                ->map(fn ($items) => [
                    'jumlah' => $items->sum(fn ($item) => (int) $item['jumlah']),
                ]);

            $barangs = BarangKantor::query()
                ->whereIn('kode_barang', $requestedItems->keys())
                ->where('kategori_barang', 'bhp')
                ->where('jenis_bhp', BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR)
                ->where('status_barang', BarangKantor::STATUS_AKTIF)
                ->lockForUpdate()
                ->get()
                ->keyBy('kode_barang');

            foreach ($requestedItems as $kodeBarang => $item) {
                $barang = $barangs->get($kodeBarang);

                if (! $barang || (int) $barang->stok < (int) $item['jumlah']) {
                    $stok = (int) ($barang?->stok ?? 0);
                    $namaBarang = $barang?->nama_barang ?? $kodeBarang;

                    throw ValidationException::withMessages([
                        'items' => "Jumlah pemakaian {$namaBarang} tidak boleh melebihi stok tersedia ({$stok}).",
                    ]);
                }

                PemakaianBHP::create([
                    'user_id' => Auth::id(), // Memaksa ID user yang login
                    'tanggal_pemakaian' => $tanggalPemakaian,
                    'alasan_kebutuhan' => $request->alasan_kebutuhan,
                    'kode_barang' => $kodeBarang,
                    'jumlah' => (int) $item['jumlah'],
                    'bukti_pendukung' => $buktiPath,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengajuan berhasil dikirim!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
