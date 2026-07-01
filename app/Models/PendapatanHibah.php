<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class PendapatanHibah extends Model
{
    protected $table = 'pendapatan_hibah';
    protected $primaryKey = 'no_hibah';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_hibah',
        'tanggal_hibah',
        'sumber_hibah',
        'jenis_hibah',
        'akun_bank_hibah',
        'akun_pendapatan_hibah',
        'nilai_hibah',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_hibah' => 'date',
        'nilai_hibah' => 'decimal:2',
    ];

    public function jurnal(): HasOne
    {
        return $this->hasOne(JurnalUmum::class, 'reff_pendapatan_hibah', 'no_hibah');
    }

    public function perolehanBarangs(): HasMany
    {
        return $this->hasMany(PerolehanBarang::class, 'pendapatan_hibah_id', 'no_hibah');
    }

    public function akunBankHibah(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'akun_bank_hibah', 'kode_akun');
    }

    public function akunPendapatanHibah(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'akun_pendapatan_hibah', 'kode_akun');
    }

    public function getDigunakanAttribute(): int
    {
        return $this->usedAmount();
    }

    public function getSisaAttribute(): int
    {
        return max(0, (int) $this->nilai_hibah - $this->usedAmount());
    }

    public function usedAmount(?string $excludePerolehanId = null): int
    {
        return (int) PerolehanBarangDetail::query()
            ->whereHas('perolehanBarang', function ($query) use ($excludePerolehanId): void {
                $query
                    ->where('pendapatan_hibah_id', $this->no_hibah)
                    ->where('sumber_perolehan', PerolehanBarang::SUMBER_HIBAH_UANG)
                    ->when($excludePerolehanId, fn ($q) => $q->where('id_perolehan_barang', '!=', $excludePerolehanId));
            })
            ->sum('total_harga_perolehan');
    }

    public function syncJurnalUmum(): void
    {
        JurnalUmum::withoutGlobalScopes()
            ->where('reff_pendapatan_hibah', $this->no_hibah)
            ->get()
            ->each(function (JurnalUmum $jurnal): void {
                $jurnal->details()->delete();
                $jurnal->delete();
            });

        if ((int) $this->nilai_hibah <= 0) {
            return;
        }

        $jurnal = JurnalUmum::create([
            'reff_pendapatan_hibah' => $this->no_hibah,
            'tanggal' => $this->tanggal_hibah,
            'deskripsi' => $this->keterangan ?: "Pendapatan hibah {$this->no_hibah}",
            'tipe_transaksi' => 'pendapatan_hibah',
        ]);

        $jurnal->details()->create([
            'kode_akun' => $this->akun_bank_hibah,
            'nominal_debit' => (int) $this->nilai_hibah,
            'nominal_kredit' => 0,
        ]);

        $jurnal->details()->create([
            'kode_akun' => $this->akun_pendapatan_hibah,
            'nominal_debit' => 0,
            'nominal_kredit' => (int) $this->nilai_hibah,
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (PendapatanHibah $record): void {
            if (blank($record->no_hibah)) {
                $record->no_hibah = self::generateNoHibah();
            }

            $record->jenis_hibah = 'hibah_uang';
        });

        static::saving(function (PendapatanHibah $record): void {
            $record->akun_bank_hibah = $record->akun_bank_hibah
                ?: Coa::query()->where('nama_akun', 'Kas Bank Hibah')->value('kode_akun');
            $record->akun_pendapatan_hibah = $record->akun_pendapatan_hibah
                ?: Coa::query()->where('nama_akun', 'Pendapatan Donasi Hibah')->value('kode_akun');

            $errors = [];

            if (blank($record->akun_bank_hibah)) {
                $errors['data.akun_bank_hibah'] = 'Tambahkan akun COA Kas Bank Hibah terlebih dahulu.';
            }

            if (blank($record->akun_pendapatan_hibah)) {
                $errors['data.akun_pendapatan_hibah'] = 'Tambahkan akun COA Pendapatan Donasi Hibah terlebih dahulu.';
            }

            if ($record->nilai_hibah !== null && (int) $record->nilai_hibah <= 0) {
                $errors['data.nilai_hibah'] = 'Nilai hibah harus lebih dari 0.';
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        });

        static::saved(fn (PendapatanHibah $record) => $record->syncJurnalUmum());

        static::deleting(function (PendapatanHibah $record): void {
            JurnalUmum::withoutGlobalScopes()
                ->where('reff_pendapatan_hibah', $record->no_hibah)
                ->get()
                ->each(function (JurnalUmum $jurnal): void {
                    $jurnal->details()->delete();
                    $jurnal->delete();
                });
        });
    }

    public static function generateNoHibah(): string
    {
        $prefix = 'PDH';
        $last = self::query()
            ->where('no_hibah', 'like', $prefix . '-%')
            ->orderByDesc('no_hibah')
            ->value('no_hibah');

        $next = $last ? ((int) preg_replace('/\D+/', '', $last)) + 1 : 1;

        return $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
