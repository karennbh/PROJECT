<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenyusutanAsetTetap extends Model
{
    public const ID_PREFIX = 'PST';
    public const ID_WIDTH = 4;

    protected $table = 'penyusutan_aset_tetap';
    protected $primaryKey = 'id_penyusutan';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_barang',
        'nama_aset',
        'status_penggunaan',
        'tanggal_diterima',
        'harga_perolehan',
        'nilai_residu',
        'umur_ekonomis_tahun',
        'beban_penyusutan_bulanan',
        'total_biaya_penyusutan',
        'status_penyusutan',
        'keterangan_kelengkapan',
    ];

    protected $casts = [
        'tanggal_diterima' => 'date',
        'harga_perolehan' => 'decimal:2',
        'nilai_residu' => 'decimal:2',
        'umur_ekonomis_tahun' => 'integer',
        'beban_penyusutan_bulanan' => 'decimal:2',
        'total_biaya_penyusutan' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // ─── Cascade Delete ──────────────────────────────────────────
        // Saat Penyusutan dihapus → detail dihapus satu per satu
        // agar event deleting di PenyusutanDetail terpicu (hapus jurnal)
        static::deleting(function (PenyusutanAsetTetap $penyusutan) {
            $penyusutan->details()->get()->each->delete();
        });

        // ─── Auto Generate ID & Hitung Beban ─────────────────────────
        static::creating(function (PenyusutanAsetTetap $p) {
            if (empty($p->id_penyusutan)) {
                $nextNumber = DB::transaction(function () {
                    $lastId = DB::table('penyusutan_aset_tetap')
                        ->where('id_penyusutan', 'like', self::ID_PREFIX . '-%')
                        ->orderByDesc('id_penyusutan')
                        ->lockForUpdate()
                        ->value('id_penyusutan');

                    return self::extractNumericPart($lastId) + 1;
                });

                $p->id_penyusutan = self::formatIdNumber($nextNumber);
            }

            if (! $p->kode_barang) {
                return;
            }

            $aset = BarangKantor::find($p->kode_barang);

            if ($aset) {
                $p->harga_perolehan     = $p->harga_perolehan ?? $aset->harga_perolehan;
                $p->nilai_residu        = $p->nilai_residu ?? $aset->nilai_residu;
                $p->umur_ekonomis_tahun = $p->umur_ekonomis_tahun ?? $aset->umur_ekonomis;
                $p->kode_barang         = $p->kode_barang ?? $aset->kode_barang;
                $p->nama_aset           = $p->nama_aset ?? $aset->nama_barang;
                $p->status_penggunaan   = $p->status_penggunaan ?? $aset->status_penggunaan ?? BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN;
                $p->tanggal_diterima = $p->tanggal_diterima ?? $aset->tanggal_diterima;

                $totalBulan = max(((int) $p->umur_ekonomis_tahun) * 12, 1);
                $harga      = (float) ($p->harga_perolehan ?? 0);
                $residu     = (float) ($p->nilai_residu ?? 0);

                // Rumus Penyusutan Garis Lurus: (Harga Perolehan - Nilai Residu) / Umur Ekonomis (dalam bulan)
                $p->beban_penyusutan_bulanan = ($harga - $residu) / $totalBulan;
                $p->total_biaya_penyusutan = $p->total_biaya_penyusutan ?? 0;
                $p->keterangan_kelengkapan = $p->keterangan_kelengkapan ?? 'Lengkap';
            }
        });

        static::saving(function (PenyusutanAsetTetap $p): void {
            if (! $p->kode_barang) {
                return;
            }

            $aset = BarangKantor::find($p->kode_barang);

            if (! $aset) {
                return;
            }

            $p->kode_barang = $aset->kode_barang;
            $p->nama_aset = $aset->nama_barang;
            $p->status_penggunaan = $aset->status_penggunaan ?: BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN;
            $p->tanggal_diterima = $aset->tanggal_diterima;
            $p->status_penyusutan = $aset->status_barang === BarangKantor::STATUS_AKTIF && $aset->isSiapPakai()
                ? BarangKantor::STATUS_AKTIF
                : BarangKantor::STATUS_TIDAK_AKTIF;
        });

        static::created(function (PenyusutanAsetTetap $p): void {
            $p->syncKeteranganKelengkapan();
        });
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function barangKantor()
    {
        return $this->belongsTo(BarangKantor::class, 'kode_barang', 'kode_barang');
    }

    public function details()
    {
        return $this->hasMany(PenyusutanDetail::class, 'penyusutan_id', 'id_penyusutan')
            ->orderBy('periode', 'asc');
    }

    public function jurnal()
    {
        return $this->hasMany(JurnalUmum::class, 'reff_penyusutan', 'id_penyusutan')
            ->where('tipe_transaksi', 'penyusutan');
    }

    // ─── Helper ───────────────────────────────────────────────────────

    /**
     * Hitung bulan terakhir yang boleh disusutkan berdasarkan umur ekonomis.
     * Contoh: mulai Jan-2023, umur 5 tahun → akhir valid Des-2027
     */
    public function bulanAkhirUmurEkonomis(): \Carbon\Carbon
    {
        $totalBulan = max(((int) $this->umur_ekonomis_tahun) * 12, 1);
        return $this->bulanMulaiPenyusutan()
            ->addMonthsNoOverflow($totalBulan - 1)
            ->endOfMonth();
    }

    public function bulanMulaiPenyusutan(): Carbon
    {
        return self::bulanMulaiPenyusutanDariTanggal($this->tanggal_diterima);
    }

    /**
     * PSAK 16: penyusutan dimulai ketika aset Siap Digunakan.
     * Tanggal Diterima menjadi dasar awal penyusutan, dengan cut-off tanggal 15.
     */
    public static function bulanMulaiPenyusutanDariTanggal(mixed $tanggalSiapDigunakan): Carbon
    {
        $tanggal = Carbon::parse($tanggalSiapDigunakan);
        $mulai = $tanggal->copy()->startOfMonth();

        return $tanggal->day > 15
            ? $mulai->addMonthNoOverflow()
            : $mulai;
    }

    public function isAktif(): bool
    {
        return $this->status_penyusutan === 'Aktif' && $this->isSiapPakai();
    }

    /**
     * Aset baru boleh disusutkan jika sudah digunakan dan punya Tanggal Diterima.
     */
    public function isSiapPakai(): bool
    {
        return in_array($this->status_penggunaan, [
            BarangKantor::STATUS_SIAP_DIGUNAKAN,
        ], true) && filled($this->tanggal_diterima);
    }

    public static function formatIdNumber(int $number): string
    {
        return self::ID_PREFIX . '-' . str_pad((string) $number, self::ID_WIDTH, '0', STR_PAD_LEFT);
    }

    public static function extractNumericPart(?string $value): int
    {
        if (blank($value)) {
            return 0;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return (int) ($digits ?: 0);
    }

    public function syncTotalBiayaPenyusutan(): void
    {
        $this->forceFill([
            'total_biaya_penyusutan' => (float) $this->details()->sum('beban_penyusutan_bulanan'),
        ])->saveQuietly();
    }

    public function syncKeteranganKelengkapan(): void
    {
        $this->forceFill([
            'keterangan_kelengkapan' => $this->buildKeteranganKelengkapan(),
            'kode_barang' => $this->kode_barang ?: $this->barangKantor?->kode_barang,
            'nama_aset' => $this->nama_aset ?: $this->barangKantor?->nama_barang,
        ])->saveQuietly();
    }

    public function buildKeteranganKelengkapan(): string
    {
        if (blank($this->tanggal_diterima)) {
            return 'Belum Siap Digunakan';
        }

        $mulai = $this->bulanMulaiPenyusutan();
        $totalBulan = max(((int) $this->umur_ekonomis_tahun) * 12, 1);
        $akhirUmur = $mulai->copy()->addMonthsNoOverflow($totalBulan - 1)->endOfMonth();
        $batasCek = self::batasPeriodeTertutup($akhirUmur);
        $cursor = $mulai->copy()->endOfMonth();
        $bulanBolong = [];

        if ($cursor->gt($batasCek)) {
            return 'Belum Waktunya';
        }

        while ($cursor->lte($batasCek)) {
            $ada = $this->details()
                ->where('periode', $cursor->toDateString())
                ->exists();

            if (! $ada) {
                $bulanBolong[] = $cursor->copy();
            }

            $cursor->addMonthNoOverflow()->endOfMonth();
        }

        if ($bulanBolong === []) {
            return 'Lengkap';
        }

        return 'Bolong: ' . self::formatRentangBulanBolong($bulanBolong);
    }

    public static function batasPeriodeTertutup(Carbon $akhirUmur): Carbon
    {
        $akhirBulanIni = now()->copy()->endOfMonth();

        $periodeTerakhirTertutup = now()->isSameDay($akhirBulanIni)
            ? $akhirBulanIni
            : now()->copy()->subMonthNoOverflow()->endOfMonth();

        return $periodeTerakhirTertutup->lt($akhirUmur)
            ? $periodeTerakhirTertutup
            : $akhirUmur;
    }

    public static function formatRentangBulanBolong(array $bulanBolong): string
    {
        $rentang = [];
        $awal = null;
        $akhir = null;

        foreach ($bulanBolong as $bulan) {
            if (! $bulan instanceof Carbon) {
                continue;
            }

            if ($awal === null) {
                $awal = $bulan->copy();
                $akhir = $bulan->copy();
                continue;
            }

            if ($bulan->copy()->startOfMonth()->equalTo($akhir->copy()->addMonthNoOverflow()->startOfMonth())) {
                $akhir = $bulan->copy();
                continue;
            }

            $rentang[] = self::formatSatuRentangBulan($awal, $akhir);
            $awal = $bulan->copy();
            $akhir = $bulan->copy();
        }

        if ($awal !== null && $akhir !== null) {
            $rentang[] = self::formatSatuRentangBulan($awal, $akhir);
        }

        return implode(', ', $rentang);
    }

    public static function formatSatuRentangBulan(Carbon $awal, Carbon $akhir): string
    {
        if ($awal->isSameMonth($akhir) && $awal->isSameYear($akhir)) {
            return $awal->translatedFormat('F Y');
        }

        return $awal->translatedFormat('F Y') . ' - ' . $akhir->translatedFormat('F Y');
    }
}
