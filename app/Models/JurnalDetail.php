<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalDetail extends Model
{
    protected $table = 'jurnal_detail';
    protected $primaryKey = 'id_jurnal_detail';

    protected $fillable = [
        'id_jurnal_umum',
        'kode_akun',
        'nominal_debit',
        'nominal_kredit',
        'keterangan',
    ];

    protected $casts = [
        'nominal_debit' => 'integer',
        'nominal_kredit' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (JurnalDetail $detail) {
            if (blank($detail->keterangan) && filled($detail->kode_akun)) {
                $detail->keterangan = Coa::where('kode_akun', $detail->kode_akun)->value('nama_akun');
            }
        });
    }

    /**
     * Nama fungsi diubah menjadi jurnalUmum agar sesuai dengan 
     * panggilan whereHas('jurnalUmum') di Widget Buku Besar.
     */
    public function jurnalUmum(): BelongsTo
    {
        return $this->belongsTo(JurnalUmum::class, 'id_jurnal_umum', 'id_jurnal_umum');
    }

    /**
     * Relasi ke COA menggunakan kolom 'kode_akun'.
     */
    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kode_akun', 'kode_akun');
    }
}
