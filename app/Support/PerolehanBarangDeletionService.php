<?php

namespace App\Support;

use App\Models\BarangKantor;
use App\Models\JurnalUmum;
use App\Models\PerolehanBarang;
use App\Models\PerolehanBarangDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PerolehanBarangDeletionService
{
    public static function deletePerolehan(PerolehanBarang $record): void
    {
        DB::transaction(function () use ($record): void {
            $record->loadMissing('details');

            foreach ($record->details as $detail) {
                $detail->delete();
            }

            self::deleteJurnals($record->id_perolehan_barang);
        });
    }

    public static function cleanupDetail(PerolehanBarangDetail $detail): void
    {
        if ($detail->kategori_barang === 'bhp') {
            self::rollbackBhpStock($detail);

            return;
        }

        if ($detail->kategori_barang === 'aset') {
            self::deleteAssetsForDetail($detail);
        }
    }

    public static function deleteJurnals(string $perolehanBarangId): void
    {
        JurnalUmum::whereIn('tipe_transaksi', ['pembelian_barang', 'perolehan_barang'])
            ->where('reff_perolehan_barang', $perolehanBarangId)
            ->get()
            ->each(function (JurnalUmum $jurnal): void {
                $jurnal->details()->delete();
                $jurnal->delete();
            });
    }

    private static function rollbackBhpStock(PerolehanBarangDetail $detail): void
    {
        $stokKeluar = PerolehanBarangDetail::convertToPcs((int) $detail->jumlah_perolehan, $detail->satuan_perolehan);
        $barang = BarangKantor::find($detail->kode_barang)
            ?? BarangKantor::where('kategori_barang', 'bhp')->where('nama_barang', $detail->nama_barang)->first();

        if (! $barang) {
            return;
        }

        $stokBaru = (int) $barang->stok - $stokKeluar;

        if ($stokBaru <= 0) {
            $barang->delete();

            return;
        }

        $barang->update(['stok' => $stokBaru]);
    }

    private static function deleteAssetsForDetail(PerolehanBarangDetail $detail): void
    {
        self::assetsForDeletion($detail)
            ->unique('kode_barang')
            ->each(function (BarangKantor $barang): void {
                $barang->penyusutans()->get()->each->delete();
                $barang->delete();
            });
    }

    private static function assetsForDeletion(PerolehanBarangDetail $detail): Collection
    {
        $linkedAssets = $detail->asetItems()->get();
        $missingCount = max(0, (int) $detail->jumlah_perolehan - $linkedAssets->count());

        if ($missingCount <= 0) {
            return $linkedAssets;
        }

        $candidatePrices = self::distributeUnitPrices(
            (int) $detail->total_harga_perolehan,
            max(1, (int) $detail->jumlah_perolehan),
        );

        $candidateQuery = BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->whereNull('perolehan_barang_detail_id')
            ->where('nama_barang', $detail->nama_barang)
            ->when($detail->jenis_aset, fn ($query) => $query->where('jenis_aset', $detail->jenis_aset))
            ->when($detail->kategori_aset_id, fn ($query) => $query->where('kategori_aset_id', $detail->kategori_aset_id))
            ->when($detail->umur_ekonomis, fn ($query) => $query->where('umur_ekonomis', $detail->umur_ekonomis))
            ->whereIn('harga_perolehan', array_unique($candidatePrices) ?: [(int) $detail->harga_perolehan]);

        if ($detail->tanggalSiapPakaiAset()) {
            $candidateQuery->whereDate('tanggal_diterima', $detail->tanggalSiapPakaiAset());
        }

        return $linkedAssets->merge(
            $candidateQuery
                ->orderBy('kode_barang')
                ->limit($missingCount)
                ->get()
        );
    }

    private static function distributeUnitPrices(int $total, int $jumlah): array
    {
        $jumlah = max(1, $jumlah);
        $base = intdiv($total, $jumlah);
        $remainder = $total - ($base * $jumlah);
        $prices = array_fill(0, $jumlah, $base);

        for ($i = 0; $i < $remainder; $i++) {
            $prices[$i]++;
        }

        return $prices;
    }
}
