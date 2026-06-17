<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PeminjamanBarang extends Model
{
   use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_MENUNGGU_VERIFIKASI_PENGEMBALIAN = 'menunggu_verifikasi_pengembalian';
    public const STATUS_KEMBALI = 'kembali';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_EXPIRED = 'expired';

    protected $table = 'peminjaman_barangs';
    protected $primaryKey = 'id_peminjaman';
    
    protected $fillable = [
        'user_id',
        'kode_barang',
        'kategori_barang',
        'nama_barang',
        'tanggal_pinjam',
        'tanggal_pengembalian',
        'alasan_peminjaman',
        'bukti_peminjaman',
        'bukti_pengembalian',
        'jumlah_pinjam',
        'status_pinjam',
    ];

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(BarangKantor::class, 'kode_barang', 'kode_barang');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function getIsTerlambatAttribute()
    {
        if ($this->status_pinjam === self::STATUS_DISETUJUI) {
            return Carbon::now()->startOfDay()
                ->gt(Carbon::parse($this->tanggal_pengembalian));
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAHAN UNTUK NOTIFIKASI TENGGAT
    |--------------------------------------------------------------------------
    */

    public function getSisaHariAttribute()
    {
        return (int) Carbon::now()
            ->startOfDay()
            ->diffInDays(Carbon::parse($this->tanggal_pengembalian)->startOfDay(), false);
    }

    public function getHariTerlambatAttribute(): int
    {
        if (! $this->is_terlambat) {
            return 0;
        }

        return abs($this->sisa_hari);
    }

    public function getStatusTenggatAttribute()
    {
        if ($this->status_pinjam !== self::STATUS_DISETUJUI) {
            return null;
        }

        if ($this->sisa_hari === 1) {
            return 'besok';
        }

        if ($this->sisa_hari === 0) {
            return 'hari_ini';
        }

        if ($this->sisa_hari < 0) {
            return 'terlambat';
        }

        return null;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status_pinjam === self::STATUS_EXPIRED;
    }

    public static function expirePendingOverdue(): int
    {
        return static::query()
            ->where('status_pinjam', self::STATUS_PENDING)
            ->whereDate('tanggal_pengembalian', '<', Carbon::today()->toDateString())
            ->update(['status_pinjam' => self::STATUS_EXPIRED]);
    }

    public function getJudulPeminjamanAttribute(): string
    {
        return 'Peminjaman Barang Kantor - ' . ($this->nama_barang ?? $this->barang?->nama_barang ?? $this->kode_barang ?? $this->id_peminjaman);
    }

    protected static function booted(): void
    {
        static::saving(function (self $peminjaman): void {
            if ($peminjaman->jumlah_pinjam !== null && (int) $peminjaman->jumlah_pinjam <= 0) {
                throw ValidationException::withMessages([
                    'data.jumlah_pinjam' => 'Jumlah pinjam harus lebih dari 0.',
                ]);
            }

            if (! $peminjaman->kode_barang) {
                return;
            }

            $barang = BarangKantor::where('kode_barang', $peminjaman->kode_barang)->first();

            $peminjaman->nama_barang = $barang?->nama_barang;
            $peminjaman->kategori_barang = $barang?->kategori_barang;
        });
    }
}
