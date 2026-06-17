<?php

namespace App\Support;

use App\Models\Coa;
use App\Models\JurnalDetail;

class KasKecilBalance
{
    public static function available(?string $excludePerolehanId = null): int
    {
        $kasKecil = Coa::query()
            ->where('nama_akun', 'Kas Kecil')
            ->first();

        if (! $kasKecil) {
            return 0;
        }

        $saldo = (int) $kasKecil->jumlah_saldo;

        $details = JurnalDetail::query()
            ->where('kode_akun', $kasKecil->kode_akun)
            ->when($excludePerolehanId, function ($query) use ($excludePerolehanId) {
                $query->whereHas('jurnalUmum', function ($jurnalQuery) use ($excludePerolehanId) {
                    $jurnalQuery->where(function ($nested) use ($excludePerolehanId) {
                        $nested
                            ->whereNull('reff_perolehan_barang')
                            ->orWhere('reff_perolehan_barang', '!=', $excludePerolehanId);
                    });
                });
            })
            ->get();

        foreach ($details as $detail) {
            $saldo += (int) $detail->nominal_debit;
            $saldo -= (int) $detail->nominal_kredit;
        }

        return $saldo;
    }
}
