<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembelianBarang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PembelianBarangController extends Controller
{
    private const MYSQL_INT_MAX = 2147483647;

    public function index(Request $request)
    {
        $users = User::where('user_group', '!=', 'admin')->get();
        $query = PengajuanPembelianBarang::with('user');

        if (Auth::user()->user_group !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->tanggal_sampai);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $riwayat = $query->latest()->limit(5)->get();

        return view('pembelian.index', compact('users', 'riwayat'));
    }

    public function store(Request $request)
    {
        // Validasi input utama dan array items
        $validated = $request->validate([
            'alasan' => 'required|string',
            'bukti_pendukung' => 'required|image|mimes:jpg,jpeg,png,webp|max:102400',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|min:3|max:50',
            'items.*.jumlah' => 'required|integer|min:1|max:' . self::MYSQL_INT_MAX,
            'items.*.kategori_barang' => 'required|in:aset,bhp',
            'items.*.perkiraan_harga' => 'required|integer|min:1000|max:' . self::MYSQL_INT_MAX,
            'items.*.link_barang' => 'nullable|url',
        ], [
            'alasan.required' => 'Alasan pengajuan wajib diisi.',
            'bukti_pendukung.required' => 'Bukti pendukung wajib diupload.',
            'bukti_pendukung.image' => 'Bukti pendukung harus berupa gambar.',
            'bukti_pendukung.mimes' => 'Bukti pendukung harus berformat jpg, jpeg, png, atau webp.',
            'bukti_pendukung.max' => 'Ukuran bukti pendukung maksimal 100MB.',
            'items.required' => 'Daftar barang wajib diisi.',
            'items.min' => 'Minimal ada 1 barang yang diajukan.',
            'items.*.nama_barang.required' => 'Nama barang wajib diisi.',
            'items.*.nama_barang.min' => 'Nama barang minimal 3 karakter.',
            'items.*.jumlah.required' => 'Jumlah wajib diisi.',
            'items.*.jumlah.integer' => 'Jumlah harus berupa angka bulat.',
            'items.*.jumlah.min' => 'Jumlah harus lebih dari 0.',
            'items.*.jumlah.max' => 'Jumlah terlalu besar.',
            'items.*.kategori_barang.required' => 'Kategori barang wajib dipilih.',
            'items.*.kategori_barang.in' => 'Kategori barang tidak valid.',
            'items.*.perkiraan_harga.required' => 'Perkiraan harga wajib diisi.',
            'items.*.perkiraan_harga.integer' => 'Perkiraan harga harus berupa angka bulat.',
            'items.*.perkiraan_harga.min' => 'Perkiraan harga minimal Rp 1.000.',
            'items.*.perkiraan_harga.max' => 'Perkiraan harga maksimal Rp 2.147.483.647.',
            'items.*.link_barang.url' => 'Link barang harus berupa URL yang valid.',
        ]);

        $subtotalErrors = [];
        foreach ($validated['items'] as $index => $item) {
            $subTotal = (int) $item['perkiraan_harga'] * (int) $item['jumlah'];

            if ($subTotal > self::MYSQL_INT_MAX) {
                $subtotalErrors["items.{$index}.perkiraan_harga"] = 'Total harga per item maksimal Rp 2.147.483.647. Kurangi harga atau jumlah.';
            }
        }

        if ($subtotalErrors !== []) {
            throw ValidationException::withMessages($subtotalErrors);
        }

        try {
            DB::beginTransaction();
            $tanggalPengajuan = today()->toDateString();

            // Handle upload bukti pendukung (satu bukti untuk satu pengajuan grup)
            $buktiPath = null;
            if ($request->hasFile('bukti_pendukung')) {
                $buktiPath = $request->file('bukti_pendukung')->store('bukti_pembelian', 'public');
            }

            // Loop untuk menyimpan setiap item barang
            foreach ($validated['items'] as $item) {
                $sub_total = $item['perkiraan_harga'] * $item['jumlah'];

                PengajuanPembelianBarang::create([
                    'user_id' => Auth::id(),
                    'nama_barang' => $item['nama_barang'],
                    'jumlah' => $item['jumlah'],
                    'kategori_barang' => $item['kategori_barang'],
                    'tanggal_pengajuan' => $tanggalPengajuan,
                    'perkiraan_harga' => $item['perkiraan_harga'],
                    'sub_total' => $sub_total,
                    'link_barang' => $item['link_barang'] ?? null,
                    'alasan' => $validated['alasan'],
                    'bukti_pendukung' => $buktiPath,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Semua item pengajuan pembelian berhasil dikirim!');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function riwayatSemua(Request $request)
    {
        $query = PengajuanPembelianBarang::with('user');

        if (Auth::user()->user_group !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $riwayat = $query->latest()->paginate(10);
        return view('pembelian.riwayat', compact('riwayat'));
    }
}
