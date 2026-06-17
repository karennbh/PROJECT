<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Support\PerolehanBarangDeletionService;

class PerolehanBarang extends Model
{
    use HasFactory;

    public const SUMBER_PEMBELIAN = 'pembelian';
    public const SUMBER_HIBAH_LEGACY = 'hibah';
    public const SUMBER_HIBAH = 'hibah_barang';
    public const SUMBER_HIBAH_UANG = 'hibah_uang';

    protected $table = 'perolehan_barang';
    protected $primaryKey = 'id_perolehan_barang';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_perolehan_barang',
        'sumber_perolehan',
        'tanggal_pembelian',
        'status_penggunaan',
        'tanggal_diterima',
        'nama_pemberi_hibah',
        'foto_nota',
        'bukti_dokumen_hibah',
        'keterangan',
        'subtotal_barang',
        'diskon_total',
        'biaya_lainnya_total',
        'grand_total',
        'total_nilai_hibah',
        'nilai_pengakuan_pendapatan_hibah_uang',
        'pendapatan_hibah_id',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'tanggal_diterima' => 'date',
        'subtotal_barang' => 'integer',
        'diskon_total' => 'integer',
        'biaya_lainnya_total' => 'integer',
        'grand_total' => 'integer',
        'total_nilai_hibah' => 'integer',
        'nilai_pengakuan_pendapatan_hibah_uang' => 'integer',
    ];

    public function details()
    {
        return $this->hasMany(
            PerolehanBarangDetail::class,
            'perolehan_barang_id',
            'id_perolehan_barang'
        );
    }

    public function pendapatanHibah()
    {
        return $this->belongsTo(PendapatanHibah::class, 'pendapatan_hibah_id', 'no_hibah');
    }

    public function jurnal()
    {
        return $this->hasMany(JurnalUmum::class, 'reff_perolehan_barang', 'id_perolehan_barang')
            ->whereIn('tipe_transaksi', ['perolehan_barang', 'pembelian_barang']);
    }

    protected static function booted(): void
    {
        static::deleting(function (PerolehanBarang $record): void {
            PerolehanBarangDeletionService::deletePerolehan($record);
        });
    }

    public function isPembelian(): bool
    {
        return ($this->sumber_perolehan ?? self::SUMBER_PEMBELIAN) === self::SUMBER_PEMBELIAN;
    }

    public function isHibah(): bool
    {
        return in_array($this->sumber_perolehan, [self::SUMBER_HIBAH_LEGACY, self::SUMBER_HIBAH, self::SUMBER_HIBAH_UANG], true);
    }

    public function isHibahBarang(): bool
    {
        return in_array($this->sumber_perolehan, [self::SUMBER_HIBAH_LEGACY, self::SUMBER_HIBAH], true);
    }

    public function isHibahUang(): bool
    {
        return $this->sumber_perolehan === self::SUMBER_HIBAH_UANG;
    }

    public function getNilaiTransaksiAttribute(): int
    {
        $totalDetail = (int) $this->details()->sum('total_harga_perolehan');

        return $this->isHibah()
            ? ((int) $this->total_nilai_hibah ?: $totalDetail)
            : ((int) $this->grand_total ?: $totalDetail);
    }

    public function getTotalHargaPerolehanAttribute(): int
    {
        return (int) $this->details()->sum('total_harga_perolehan');
    }

    public function getJumlahItemAttribute(): int
    {
        return (int) $this->details()->count();
    }

    public function syncJurnalUmum(): void
    {
        $this->loadMissing('details');

        $subtotalBarang = (int) $this->details->sum('total_harga');
        $totalNilaiPerolehan = (int) $this->details->sum('total_harga_perolehan');
        $diskonTotal = max(0, (int) $this->details->sum('alokasi_diskon'));
        $biayaLainnyaTotal = max(0, (int) $this->details->sum('alokasi_biaya_lainnya'));
        $grandTotalTransaksi = (int) $this->grand_total;

        if ($grandTotalTransaksi <= 0) {
            $grandTotalTransaksi = $totalNilaiPerolehan ?: ($subtotalBarang - $diskonTotal + $biayaLainnyaTotal);
        }

        $totalDibayar = $this->isHibah()
            ? ((int) $this->total_nilai_hibah ?: $totalNilaiPerolehan)
            : $grandTotalTransaksi;

        $syncData = [
            'subtotal_barang' => $subtotalBarang,
        ];

        if ($this->isHibah()) {
            $syncData['total_nilai_hibah'] = $totalDibayar;
            $syncData['grand_total'] = $totalDibayar;
            $syncData['diskon_total'] = 0;
            $syncData['biaya_lainnya_total'] = 0;
        } else {
            $syncData['grand_total'] = $totalDibayar;
            $syncData['total_nilai_hibah'] = 0;
            $syncData['diskon_total'] = $diskonTotal;
            $syncData['biaya_lainnya_total'] = $biayaLainnyaTotal;
        }

        if (
            $this->subtotal_barang !== $syncData['subtotal_barang'] ||
            $this->diskon_total !== $syncData['diskon_total'] ||
            $this->biaya_lainnya_total !== $syncData['biaya_lainnya_total'] ||
            $this->grand_total !== $syncData['grand_total'] ||
            $this->total_nilai_hibah !== $syncData['total_nilai_hibah']
        ) {
            $this->forceFill($syncData)->saveQuietly();
        }

        JurnalUmum::whereIn('tipe_transaksi', ['pembelian_barang', 'perolehan_barang'])
            ->where('reff_perolehan_barang', $this->id_perolehan_barang)
            ->get()
            ->each(function (JurnalUmum $jurnal): void {
                $jurnal->details()->delete();
                $jurnal->delete();
            });

        if ($totalDibayar <= 0 || $this->details->isEmpty()) {
            return;
        }

        $akunKredit = match (true) {
            $this->isHibahBarang() => Coa::query()->where('nama_akun', 'Penerimaan Hibah Barang')->first(),
            $this->isHibahUang() => Coa::query()->where('kode_akun', $this->pendapatanHibah?->akun_bank_hibah)->first()
                ?: Coa::query()->where('nama_akun', 'Kas Bank Hibah')->first(),
            default => Coa::query()->where('nama_akun', 'Kas Kecil')->first(),
        };

        if (! $akunKredit) {
            return;
        }

        $totalPerAkun = $this->details
            ->groupBy(fn (PerolehanBarangDetail $detail) => $detail->kategori_barang === 'aset'
                ? self::assetAccountNameForJenis($detail->jenis_aset)
                : self::bhpAccountNameForJenis($detail->jenis_bhp))
            ->map(fn (Collection $group) => (int) $group->sum('total_harga_perolehan'))
            ->filter(fn (int $total) => $total > 0);

        if ($totalPerAkun->isEmpty()) {
            return;
        }

        $selisihPembulatan = $totalDibayar - (int) $totalPerAkun->sum();

        if ($selisihPembulatan !== 0) {
            $akunPenyesuaian = $totalPerAkun->sortDesc()->keys()->first();
            $totalPerAkun->put($akunPenyesuaian, max(0, (int) $totalPerAkun->get($akunPenyesuaian) + $selisihPembulatan));
            $totalPerAkun = $totalPerAkun->filter(fn (int $total) => $total > 0);
        }

        $akunDebitMap = Coa::query()
            ->whereIn('nama_akun', $totalPerAkun->keys()->all())
            ->get()
            ->keyBy('nama_akun');

        if ($akunDebitMap->count() !== $totalPerAkun->count()) {
            return;
        }

        $labelSumber = match (true) {
            $this->isHibahBarang() => 'Hibah barang',
            $this->isHibahUang() => 'Pembelian aset dari hibah uang',
            default => 'Perolehan barang',
        };

        $jurnal = JurnalUmum::create([
            'tanggal' => $this->tanggal_pembelian ?? now(),
            'deskripsi' => "{$labelSumber} {$this->id_perolehan_barang}",
            'tipe_transaksi' => 'perolehan_barang',
            'reff_perolehan_barang' => $this->id_perolehan_barang,
        ]);

        foreach ($totalPerAkun as $namaAkun => $total) {
            $akunDebit = $akunDebitMap->get($namaAkun);

            $jurnal->details()->create([
                'kode_akun' => $akunDebit->kode_akun,
                'nominal_debit' => $total,
                'nominal_kredit' => 0,
            ]);
        }

        $jurnal->details()->create([
            'kode_akun' => $akunKredit->kode_akun,
            'nominal_debit' => 0,
            'nominal_kredit' => $totalDibayar,
        ]);
    }

    private static function assetAccountNameForJenis(?string $jenisAset): string
    {
        return match ($jenisAset) {
            'sarana_pendidikan_laboratorium' => 'Sarana Pendidikan Laboratorium',
            'inventaris_kantor' => 'Inventaris Kantor',
            'kendaraan' => 'Kendaraan Bermotor',
            default => 'Sarana Pendidikan Laboratorium',
        };
    }

    private static function bhpAccountNameForJenis(?string $jenisBhp): string
    {
        return match ($jenisBhp) {
            BarangKantor::JENIS_BHP_INVENTARIS_KANTOR => 'BPP Inventaris Kantor',
            default => 'Beban ATK Operasional',
        };
    }
}
