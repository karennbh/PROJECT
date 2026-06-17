<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class KategoriAsetTetap extends Model
{
    use HasFactory;

    protected $table = 'kategori_aset_tetap';
    protected $primaryKey = 'id_kategori_aset';
    public $incrementing = false;       // penting
    protected $keyType = 'string';      // penting

    protected $fillable = [
        'nama_kategori_aset',
        'umur_ekonomis',
        'tarif_penyusutan',
        'keterangan',
    ];

    public function barangKantors()
    {
        return $this->hasMany(BarangKantor::class, 'kategori_aset_id', 'id_kategori_aset');
    }

    public function barangMasukDetails()
    {
        return $this->hasMany(PerolehanBarangDetail::class, 'kategori_aset_id', 'id_kategori_aset');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            // hitung ulang berdasarkan jumlah record supaya kalau dihapus balik ke 0001
            $count = static::count() + 1;

            $model->id_kategori_aset = 'KTGRAST' . str_pad($count, 4, '0', STR_PAD_LEFT);
        });

        static::saving(function (self $model) {
            $umurEkonomis = (int) $model->umur_ekonomis;

            if ($umurEkonomis < 5) {
                throw ValidationException::withMessages([
                    'data.umur_ekonomis' => 'Umur ekonomis tidak boleh kurang dari Kelompok 1, minimal 5 tahun.',
                ]);
            }

            $umurSudahDipakai = self::query()
                ->where('umur_ekonomis', $umurEkonomis)
                ->when($model->exists, fn ($query) => $query->whereKeyNot($model->getKey()))
                ->exists();

            if ($umurSudahDipakai) {
                throw ValidationException::withMessages([
                    'data.umur_ekonomis' => 'Umur ekonomis tersebut sudah digunakan oleh kelompok aset lain.',
                ]);
            }

            if ($umurEkonomis > 0) {
                $model->tarif_penyusutan = round(100 / $umurEkonomis, 2);
            }
        });
    }
}
