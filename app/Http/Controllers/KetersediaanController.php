<?php

namespace App\Http\Controllers;

use App\Models\BarangKantor;
use Illuminate\Http\Request;

class KetersediaanController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangKantor::query();

        if ($request->filled('jenis') && $request->jenis !== 'all') {
            match ($request->jenis) {
                'aset' => $query->where('kategori_barang', 'aset'),
                'bhp_inventaris' => $query
                    ->where('kategori_barang', 'bhp')
                    ->where('jenis_bhp', BarangKantor::JENIS_BHP_INVENTARIS_KANTOR),
                'bhp_atk' => $query
                    ->where('kategori_barang', 'bhp')
                    ->where('jenis_bhp', BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR),
                default => null,
            };
        }

        $query->when($request->search, function ($q) use ($request) {
            $q->where(function($inner) use ($request) {
                $inner->where('nama_barang','like','%'.$request->search.'%')
                      ->orWhere('kode_barang','like','%'.$request->search.'%');
            });
        });

        if ($request->status && $request->status != 'all') {
            if ($request->status == 'ready') {
                // Tersedia: Aktif, stok > 0, status_pinjam Tersedia
                $query->where('status_barang', 'Aktif')
                      ->where('stok', '>', 0)
                      ->where(function ($q): void {
                          $q->where(function ($q): void {
                              $q->where('kategori_barang', 'bhp')
                                ->where(function ($q): void {
                                    $q->whereNull('status_pinjam')
                                      ->orWhereNotIn('status_pinjam', [
                                          BarangKantor::STATUS_PINJAM_DIPINJAM,
                                          BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN,
                                          BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN,
                                      ]);
                                });
                          })
                            ->orWhere(function ($q): void {
                                $q->where('status_pinjam', BarangKantor::STATUS_PINJAM_TERSEDIA)
                                  ->whereIn('status_penggunaan', [BarangKantor::STATUS_SIAP_DIGUNAKAN])
                                  ->whereNotNull('tanggal_diterima');
                            });
                      });
            } elseif ($request->status == 'menipis') {
                // Menipis: hanya BHP ATK
                $query->bhpStokMenipis()
                      ->where('jenis_bhp', BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR);
            } elseif ($request->status == 'dipinjam') {
                $query->where('status_pinjam', BarangKantor::STATUS_PINJAM_DIPINJAM);
            } elseif ($request->status == 'habis') {
                $query->where('stok', '<=', 0)
                      ->whereNotIn('status_pinjam', [
                          BarangKantor::STATUS_PINJAM_DIPINJAM,
                          BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN,
                          BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN,
                      ]);
            } elseif ($request->status == 'didistribusikan') {
                $query->where('status_pinjam', BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN);
            } elseif ($request->status == 'tidak_dipinjamkan') {
                $query->where('status_pinjam', BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN);
            } elseif ($request->status == 'unavailable') {
                $query->where(function ($q): void {
                    $q->where('status_barang', '!=', 'Aktif')
                      ->orWhere('stok', '<=', 0)
                      ->orWhereIn('status_pinjam', [
                          BarangKantor::STATUS_PINJAM_DIPINJAM,
                          BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN,
                          BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN,
                      ])
                      ->orWhere(function ($q): void {
                          $q->where('kategori_barang', 'aset')
                            ->where(function ($q): void {
                                $q->whereNull('tanggal_diterima')
                                  ->orWhere('status_penggunaan', BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN);
                            });
                      });
                });
            }
        }

        $barangs = $query->latest()->paginate(10)->withQueryString();
        return view('ketersediaan.index', compact('barangs'));
    }
}
