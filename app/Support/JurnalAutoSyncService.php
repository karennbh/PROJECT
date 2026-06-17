<?php

namespace App\Support;

use App\Models\PerolehanBarang;
use App\Models\Coa;
use App\Models\JurnalDetail;
use App\Models\JurnalUmum;
use App\Models\PenyusutanDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JurnalAutoSyncService
{
    public static function syncAll(): void
    {
        DB::transaction(function (): void {
            self::syncPerolehanBarangJournals();
            self::syncPenyusutanJournals();
        });
    }

    public static function syncPerolehanBarangJournals(): void
    {
        PerolehanBarang::query()
            ->with('details')
            ->get()
            ->each(function (PerolehanBarang $perolehanBarang): void {
                $perolehanBarang->syncJurnalUmum();
            });
    }

    public static function syncPenyusutanJournals(): void
    {
        $akunBeban = Coa::query()->where('nama_akun', 'Beban Penyusutan')->first();
        $akunAkumulasi = Coa::query()->where('nama_akun', 'Akumulasi Penyusutan')->first();

        PenyusutanDetail::query()
            ->with(['penyusutan.barangKantor'])
            ->get()
            ->each(function (PenyusutanDetail $detail) use ($akunBeban, $akunAkumulasi): void {
                if ($detail->jurnal_umum_id) {
                    JurnalUmum::find($detail->jurnal_umum_id)?->delete();
                } else {
                    JurnalUmum::query()
                        ->where('tipe_transaksi', 'penyusutan')
                        ->where('reff_penyusutan', $detail->penyusutan_id)
                        ->whereDate('tanggal', $detail->periode)
                        ->get()
                        ->each(fn (JurnalUmum $jurnal) => $jurnal->delete());
                }

                if (! $akunBeban || ! $akunAkumulasi) {
                    $detail->forceFill(['jurnal_umum_id' => null])->saveQuietly();

                    return;
                }

                $namaAset = $detail->penyusutan?->barangKantor?->nama_barang ?? $detail->penyusutan_id;
                $periode = Carbon::parse($detail->periode);

                $jurnal = JurnalUmum::create([
                    'reff_penyusutan' => $detail->penyusutan_id,
                    'tanggal' => $periode->toDateString(),
                    'deskripsi' => 'Penyusutan: ' . $namaAset . ' (' . $periode->translatedFormat('M Y') . ')',
                    'tipe_transaksi' => 'penyusutan',
                ]);

                JurnalDetail::create([
                    'id_jurnal_umum' => $jurnal->id_jurnal_umum,
                    'kode_akun' => $akunBeban->kode_akun,
                    'nominal_debit' => (int) round((float) $detail->beban_penyusutan_bulanan),
                    'nominal_kredit' => 0,
                    'keterangan' => 'Beban Penyusutan',
                ]);

                JurnalDetail::create([
                    'id_jurnal_umum' => $jurnal->id_jurnal_umum,
                    'kode_akun' => $akunAkumulasi->kode_akun,
                    'nominal_debit' => 0,
                    'nominal_kredit' => (int) round((float) $detail->beban_penyusutan_bulanan),
                    'keterangan' => 'Akumulasi Penyusutan',
                ]);

                $detail->forceFill(['jurnal_umum_id' => $jurnal->id_jurnal_umum])->saveQuietly();
            });
    }
}

