<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class PengisianKasKecil extends Model
{
    protected $table = 'pengisian_kas_kecil';

    protected $primaryKey = 'no_transaksi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'no_transaksi',
        'tanggal',
        'akun_kas_kecil',
        'akun_sumber_dana',
        'nominal',
        'keterangan',
        'bukti',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'integer',
    ];

    public function jurnal(): HasOne
    {
        return $this->hasOne(JurnalUmum::class, 'reff_pengisian_kas_kecil', 'no_transaksi');
    }

    public function akunKasKecil(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'akun_kas_kecil', 'kode_akun');
    }

    public function akunSumberDana(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'akun_sumber_dana', 'kode_akun');
    }

    public function syncJurnalUmum(): void
    {
        JurnalUmum::where('reff_pengisian_kas_kecil', $this->no_transaksi)
            ->get()
            ->each(function (JurnalUmum $jurnal): void {
                $jurnal->details()->delete();
                $jurnal->delete();
            });

        if ((int) $this->nominal <= 0) {
            return;
        }

        $jurnal = JurnalUmum::create([
            'reff_pengisian_kas_kecil' => $this->no_transaksi,
            'tanggal' => $this->tanggal,
            'deskripsi' => $this->keterangan ?: "Pengisian kas kecil {$this->no_transaksi}",
            'tipe_transaksi' => 'pengisian_kas_kecil',
        ]);

        $jurnal->details()->create([
            'kode_akun' => $this->akun_kas_kecil,
            'nominal_debit' => (int) $this->nominal,
            'nominal_kredit' => 0,
        ]);

        $jurnal->details()->create([
            'kode_akun' => $this->akun_sumber_dana,
            'nominal_debit' => 0,
            'nominal_kredit' => (int) $this->nominal,
        ]);
    }

    public static function availableSumberDana(?string $kodeAkun, ?string $excludeNoTransaksi = null): int
    {
        if (blank($kodeAkun)) {
            return 0;
        }

        $coa = Coa::query()->whereKey($kodeAkun)->first();

        if (! $coa) {
            return 0;
        }

        $saldo = (int) $coa->jumlah_saldo;

        $details = JurnalDetail::query()
            ->where('kode_akun', $kodeAkun)
            ->when($excludeNoTransaksi, function ($query) use ($excludeNoTransaksi): void {
                $query->whereHas('jurnalUmum', function ($jurnalQuery) use ($excludeNoTransaksi): void {
                    $jurnalQuery->where(function ($nested) use ($excludeNoTransaksi): void {
                        $nested
                            ->whereNull('reff_pengisian_kas_kecil')
                            ->orWhere('reff_pengisian_kas_kecil', '!=', $excludeNoTransaksi);
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

    protected static function booted(): void
    {
        static::creating(function (PengisianKasKecil $record): void {
            if (blank($record->no_transaksi)) {
                $record->no_transaksi = self::generateNoTransaksi($record->tanggal ?: now()->toDateString());
            }
        });

        static::saving(function (PengisianKasKecil $record): void {
            if ($record->nominal !== null && (int) $record->nominal <= 0) {
                throw ValidationException::withMessages([
                    'data.nominal' => 'Nominal pengisian kas kecil harus lebih dari 0.',
                ]);
            }

            $nominal = (int) $record->nominal;
            $available = self::availableSumberDana(
                $record->akun_sumber_dana,
                $record->exists ? (string) $record->getKey() : null,
            );

            if ($nominal > $available) {
                throw ValidationException::withMessages([
                    'data.nominal' => 'Saldo Kas Pengeluaran Institusi tidak mencukupi. Isi saldo COA Kas Pengeluaran Institusi terlebih dahulu. Sisa saldo: Rp ' . number_format($available, 0, ',', '.'),
                ]);
            }
        });

        static::saved(function (PengisianKasKecil $record): void {
            $record->syncJurnalUmum();
        });

        static::deleting(function (PengisianKasKecil $record): void {
            JurnalUmum::where('reff_pengisian_kas_kecil', $record->no_transaksi)
                ->get()
                ->each(function (JurnalUmum $jurnal): void {
                    $jurnal->details()->delete();
                    $jurnal->delete();
                });
        });
    }

    public static function generateNoTransaksi(mixed $tanggal): string
    {
        $prefix = 'PKK';
        $last = self::query()
            ->where('no_transaksi', 'like', $prefix . '-%')
            ->orderByDesc('no_transaksi')
            ->value('no_transaksi');

        $next = $last ? ((int) preg_replace('/\D+/', '', $last)) + 1 : 1;

        return $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
