<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $table = 'coa';
    protected $primaryKey = 'kode_akun';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'header_akun',
        'saldo',
        'jumlah_saldo',
    ];

    protected $casts = [
        'jumlah_saldo' => 'integer',
    ];

    // public function jurnalDetail()
    // {
    //     return $this->hasMany(JurnalDetail::class, 'coa_id');
    // }
    public function jurnalDetails()
    {
        // jurnal_detail.kode_akun -> coa.kode_akun
        return $this->hasMany(\App\Models\JurnalDetail::class, 'kode_akun', 'kode_akun');
    }

    public function pengisianKasKecilsSebagaiKasKecil()
    {
        return $this->hasMany(PengisianKasKecil::class, 'akun_kas_kecil', 'kode_akun');
    }

    public function pengisianKasKecilsSebagaiSumberDana()
    {
        return $this->hasMany(PengisianKasKecil::class, 'akun_sumber_dana', 'kode_akun');
    }

    public function pendapatanHibahsSebagaiBank()
    {
        return $this->hasMany(PendapatanHibah::class, 'akun_bank_hibah', 'kode_akun');
    }

    public function pendapatanHibahsSebagaiPendapatan()
    {
        return $this->hasMany(PendapatanHibah::class, 'akun_pendapatan_hibah', 'kode_akun');
    }

    // SALDO NORMAL DEBIT/KREDIT
    public function isNormalDebit(): bool
    {
        return in_array($this->header_akun, ['Harta', 'Beban'], true);
    }

    /**
     * Boot the model to handle cascading deletes to the parent JurnalUmum
     * when a COA is deleted, preventing empty/dangling Jurnals.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($coa) {
            // Find all unique Jurnal Umum IDs that reference this COA
            $jurnalUmumIds = \App\Models\JurnalDetail::where('kode_akun', $coa->kode_akun)
                ->pluck('id_jurnal_umum')
                ->unique();
                
            // Delete the parent JurnalUmum records.
            // Since JurnalUmum -> JurnalDetail is usually a complete transaction,
            // removing one leg of the transaction (the COA) invalidates the whole Jurnal.
            if ($jurnalUmumIds->isNotEmpty()) {
                \App\Models\JurnalUmum::whereIn('id_jurnal_umum', $jurnalUmumIds)->delete();
            }
        });

        static::created(function (): void {
            self::syncPendingTransactionJournals();
        });
        
        static::updating(function ($coa) {
            // If the kode_akun is changing, we must manually update the JurnalDetails
            // because sometimes Eloquent doesn't trigger the DB-level cascading update easily if it's done via mass update.
            if ($coa->isDirty('kode_akun')) {
                $oldKode = $coa->getOriginal('kode_akun');
                $newKode = $coa->kode_akun;
                \App\Models\JurnalDetail::where('kode_akun', $oldKode)->update(['kode_akun' => $newKode]);
            }
        });
    }

    private static function syncPendingTransactionJournals(): void
    {
        \App\Models\PerolehanBarang::query()
            ->whereDoesntHave('jurnal')
            ->with(['details', 'pendapatanHibah'])
            ->chunk(50, function ($records): void {
                foreach ($records as $record) {
                    try {
                        $record->syncJurnalUmum();
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            });

        \App\Models\PendapatanHibah::query()
            ->whereDoesntHave('jurnal')
            ->chunk(50, function ($records): void {
                foreach ($records as $record) {
                    try {
                        if (blank($record->akun_bank_hibah)) {
                            $record->akun_bank_hibah = self::query()
                                ->where('nama_akun', 'Kas Bank Hibah')
                                ->value('kode_akun');
                        }

                        if (blank($record->akun_pendapatan_hibah)) {
                            $record->akun_pendapatan_hibah = self::query()
                                ->where('nama_akun', 'Pendapatan Donasi Hibah')
                                ->value('kode_akun');
                        }

                        if (blank($record->akun_bank_hibah) || blank($record->akun_pendapatan_hibah)) {
                            continue;
                        }

                        $record->saveQuietly();
                        $record->syncJurnalUmum();
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            });

        \App\Models\PengisianKasKecil::query()
            ->whereDoesntHave('jurnal')
            ->chunk(50, function ($records): void {
                foreach ($records as $record) {
                    try {
                        if (filled($record->akun_kas_kecil) && filled($record->akun_sumber_dana)) {
                            $record->syncJurnalUmum();
                        }
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                }
            });
    }
}
