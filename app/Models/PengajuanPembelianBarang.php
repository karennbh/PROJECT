<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PengajuanPembelianBarang extends Model
{
    /** @use HasFactory<\Database\Factories\PengajuanPembelianBarangFactory> */
    use HasFactory;
    protected $table = 'pembelian_barangs';
    protected $primaryKey = 'id_pembelian_barang_kantor';
    protected $guarded = ['id_pembelian_barang_kantor'];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    protected static function booted(): void
    {
        static::saving(function (self $pengajuan): void {
            $labels = [
                'jumlah' => 'Jumlah',
                'perkiraan_harga' => 'Perkiraan harga',
                'sub_total' => 'Total harga',
            ];

            $errors = [];

            foreach ($labels as $field => $label) {
                if ($pengajuan->{$field} !== null && (int) $pengajuan->{$field} <= 0) {
                    $errors["data.{$field}"] = "{$label} harus lebih dari 0.";
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        });
    }
}
