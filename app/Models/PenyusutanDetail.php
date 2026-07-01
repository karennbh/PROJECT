<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyusutanDetail extends Model
{
    protected $table = 'penyusutan_details';

    protected $primaryKey = 'id_penyusutan_detail';

    protected $fillable = [
        'penyusutan_id',
        'periode',
        'beban_penyusutan_bulanan',
        'akumulasi',
        'nilai_buku',
        'jurnal_umum_id',
    ];

    protected $casts = [
        'beban_penyusutan_bulanan' => 'decimal:2',
        'akumulasi' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
    ];

    public function penyusutan()
    {
        return $this->belongsTo(PenyusutanAsetTetap::class, 'penyusutan_id', 'id_penyusutan');
    }

    public function jurnalUmum()
    {
        return $this->belongsTo(JurnalUmum::class, 'jurnal_umum_id', 'id_jurnal_umum');
    }

    protected static function booted(): void
    {
        static::saved(function (PenyusutanDetail $detail): void {
            $detail->penyusutan?->syncTotalBiayaPenyusutan();
            $detail->penyusutan?->syncKeteranganKelengkapan();
        });

        static::deleting(function (PenyusutanDetail $detail): void {
            if ($detail->jurnal_umum_id) {
                JurnalUmum::find($detail->jurnal_umum_id)?->delete();
            }
        });

        static::deleted(function (PenyusutanDetail $detail): void {
            $detail->penyusutan?->syncTotalBiayaPenyusutan();
            $detail->penyusutan?->syncKeteranganKelengkapan();
        });
    }
}
