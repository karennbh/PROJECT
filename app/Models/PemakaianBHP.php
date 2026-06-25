<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PemakaianBHP extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_bhp';
    protected $primaryKey = 'id_pemakaian';
    
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'kode_barang',
        'nama_barang',
        'tanggal_pemakaian',
        'jumlah',
        'alasan_kebutuhan',
        'bukti_pendukung',
        'status',
    ];

    protected $casts = [
        'tanggal_pemakaian' => 'date',
        'jumlah' => 'integer',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Relasi ke BarangKantor
     */
    public function barang()
    {
        return $this->belongsTo(BarangKantor::class, 'kode_barang', 'kode_barang');
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeByDateRange($query, $tanggalDari, $tanggalSampai)
    {
        return $query->whereBetween('tanggal_pemakaian', [$tanggalDari, $tanggalSampai]);
    }

    /**
     * Scope untuk filter berdasarkan user (untuk non-admin)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected static function booted(): void
    {
        static::saving(function (self $pemakaian): void {
            if ($pemakaian->jumlah !== null && (int) $pemakaian->jumlah <= 0) {
                throw ValidationException::withMessages([
                    'data.jumlah' => 'Jumlah pemakaian harus lebih dari 0.',
                ]);
            }

            if (! $pemakaian->kode_barang) {
                return;
            }

            $pemakaian->nama_barang = BarangKantor::where('kode_barang', $pemakaian->kode_barang)
                ->value('nama_barang');
        });
    }
}
