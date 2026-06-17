<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{
    protected $table = 'jurnals';
    protected $primaryKey = 'id_jurnal_umum';

    public $timestamps = true;

    protected $fillable = [
        'reff_perolehan_barang',
        'reff_penyusutan',
        'reff_pengisian_kas_kecil',
        'reff_pendapatan_hibah',
        'tanggal',
        'deskripsi',
        'tipe_transaksi',
    ];

    protected static function booted(): void
    {
        static::deleting(function (JurnalUmum $jurnal) {
            $jurnal->details()->delete();
            $jurnal->penyusutanDetails()->update(['jurnal_umum_id' => null]);
        });

        static::addGlobalScope('ref_exists', function (Builder $builder) {
            $builder->where(function ($query) {
                $query->whereNotIn('tipe_transaksi', ['pembelian_barang', 'perolehan_barang'])
                    ->orWhereExists(function ($q) {
                        $q->selectRaw(1)
                            ->from('perolehan_barang')
                            ->whereColumn('perolehan_barang.id_perolehan_barang', 'jurnals.reff_perolehan_barang');
                    });
            });
        });
    }

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'id_jurnal_umum', 'id_jurnal_umum');
    }

    public function perolehanBarang()
    {
        return $this->belongsTo(PerolehanBarang::class, 'reff_perolehan_barang', 'id_perolehan_barang');
    }

    public function penyusutan()
    {
        return $this->belongsTo(PenyusutanAsetTetap::class, 'reff_penyusutan', 'id_penyusutan');
    }

    public function penyusutanDetails()
    {
        return $this->hasMany(PenyusutanDetail::class, 'jurnal_umum_id', 'id_jurnal_umum');
    }

    public function pengisianKasKecil()
    {
        return $this->belongsTo(PengisianKasKecil::class, 'reff_pengisian_kas_kecil', 'no_transaksi');
    }

    public function pendapatanHibah()
    {
        return $this->belongsTo(PendapatanHibah::class, 'reff_pendapatan_hibah', 'no_hibah');
    }

    public function getReffTransaksiAttribute(): string
    {
        return $this->reff_penyusutan
            ?: $this->reff_perolehan_barang
            ?: $this->reff_pengisian_kas_kecil
            ?: $this->reff_pendapatan_hibah
            ?: '-';
    }

    public function referensi()
    {
        return match ($this->tipe_transaksi) {
            'perolehan_barang',
            'pembelian_barang' => $this->perolehanBarang(),
            'penyusutan' => $this->penyusutan(),
            'pengisian_kas_kecil' => $this->pengisianKasKecil(),
            'pendapatan_hibah' => $this->pendapatanHibah(),
            default => null,
        };
    }
}
