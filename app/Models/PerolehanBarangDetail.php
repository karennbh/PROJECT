<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Support\PerolehanBarangDeletionService;

class PerolehanBarangDetail extends Model
{
    use HasFactory;

    public const SATUAN_PEROLEHAN_PCS = 'Pcs';
    public const SATUAN_PEROLEHAN_UNIT = 'Unit';
    public const SATUAN_PEROLEHAN_PACK = 'Pack';
    public const SATUAN_PEROLEHAN_KOTAK = 'Kotak';
    public const SATUAN_PEROLEHAN_RIM = 'Rim';
    public const SATUAN_PEROLEHAN_BOX = 'Box';

    /**
     * Konversi: 1 Box = 5 Rim
     */
    public const KONVERSI_BOX_KE_RIM = 5;

    protected $table = 'perolehan_barang_detail';
    protected $primaryKey = 'id_perolehan_barang_detail';

    protected $fillable = [
        'perolehan_barang_id',
        'nama_barang',
        'kategori_barang',
        'jenis_aset',
        'jenis_bhp',
        'kategori_aset_id',
        'umur_ekonomis',
        'nilai_residu',
        'jumlah_perolehan',
        'satuan_perolehan',
        'harga_satuan',
        'total_harga',
        'persentase_subtotal',
        'alokasi_diskon',
        'alokasi_biaya_lainnya',
        'harga_perolehan',
        'total_harga_perolehan',
        'kode_barang',
    ];

    protected $casts = [
        'nilai_residu' => 'decimal:2',
        'jumlah_perolehan' => 'integer',
        'harga_satuan' => 'decimal:2',
        'total_harga' => 'decimal:2',
        'persentase_subtotal' => 'float',
        'alokasi_diskon' => 'decimal:2',
        'alokasi_biaya_lainnya' => 'decimal:2',
        'harga_perolehan' => 'decimal:2',
        'total_harga_perolehan' => 'decimal:2',
    ];

    public function perolehanBarang()
    {
        return $this->belongsTo(PerolehanBarang::class, 'perolehan_barang_id', 'id_perolehan_barang');
    }

    public function barangKantor()
    {
        return $this->belongsTo(BarangKantor::class, 'kode_barang', 'kode_barang');
    }

    public function asetItems()
    {
        return $this->hasMany(BarangKantor::class, 'perolehan_barang_detail_id')
            ->where('kategori_barang', 'aset');
    }

    public function kategoriAset()
    {
        return $this->belongsTo(KategoriAsetTetap::class, 'kategori_aset_id', 'id_kategori_aset');
    }

    public static function satuanPerolehanOptions(): array
    {
        return [
            self::SATUAN_PEROLEHAN_PCS   => 'Pcs',
            self::SATUAN_PEROLEHAN_UNIT  => 'Unit',
            self::SATUAN_PEROLEHAN_PACK  => 'Pack',
            self::SATUAN_PEROLEHAN_KOTAK => 'Kotak',
            self::SATUAN_PEROLEHAN_RIM   => 'Rim',
            self::SATUAN_PEROLEHAN_BOX   => 'Box (1 Box = 5 Rim)',
        ];
    }

    /**
     * Kembalikan opsi satuan perolehan yang relevan untuk satuan master data tertentu.
     * - Rim → bisa pilih Rim (sama) atau Box (1 Box = 5 Rim)
     * - Lainnya → hanya satuan yang sama
     */
    public static function satuanPerolehanOptionsForSatuan(?string $satuanMaster): array
    {
        if ($satuanMaster === self::SATUAN_PEROLEHAN_RIM) {
            return [
                self::SATUAN_PEROLEHAN_RIM => 'Rim',
                self::SATUAN_PEROLEHAN_BOX => 'Box (1 Box = 5 Rim)',
            ];
        }

        // Satuan lain: opsi perolehan = satuan itu sendiri saja
        return array_filter(self::satuanPerolehanOptions(), fn ($key) => $key === $satuanMaster, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Hitung jumlah stok dalam satuan master data setelah konversi.
     * Saat ini hanya Box → Rim yang dikonversi (1 Box = 5 Rim).
     * Satuan lain dikembalikan apa adanya.
     */
    public static function convertToMasterUnit(int $jumlah, ?string $satuanPerolehan): int
    {
        return match ($satuanPerolehan) {
            self::SATUAN_PEROLEHAN_BOX => $jumlah * self::KONVERSI_BOX_KE_RIM,
            default                    => $jumlah,
        };
    }

    /**
     * @deprecated Gunakan convertToMasterUnit() — nama lama dipertahankan agar kode lain tidak rusak.
     */
    public static function convertToPcs(int $jumlah, ?string $satuanPerolehan): int
    {
        return self::convertToMasterUnit($jumlah, $satuanPerolehan);
    }

    public static function resolveBarangKantorSatuan(?string $satuanPerolehan): string
    {
        return match ($satuanPerolehan) {
            self::SATUAN_PEROLEHAN_PACK  => self::SATUAN_PEROLEHAN_PACK,
            self::SATUAN_PEROLEHAN_KOTAK => self::SATUAN_PEROLEHAN_KOTAK,
            self::SATUAN_PEROLEHAN_UNIT  => self::SATUAN_PEROLEHAN_UNIT,
            self::SATUAN_PEROLEHAN_RIM   => self::SATUAN_PEROLEHAN_RIM,
            // Box dibeli tapi master data tetap Rim
            self::SATUAN_PEROLEHAN_BOX   => self::SATUAN_PEROLEHAN_RIM,
            default                      => self::SATUAN_PEROLEHAN_PCS,
        };
    }

    protected static function booted(): void
    {
        static::saving(function (PerolehanBarangDetail $detail): void {
            if ($detail->jumlah_perolehan !== null && (int) $detail->jumlah_perolehan <= 0) {
                throw ValidationException::withMessages([
                    'data.jumlah_perolehan' => 'Jumlah perolehan harus lebih dari 0.',
                ]);
            }

            if ($detail->kategori_barang === 'bhp' && $detail->kode_barang && empty($detail->nama_barang)) {
                $bk = BarangKantor::find($detail->kode_barang);
                $detail->nama_barang = $bk?->nama_barang;
            }

            if ($detail->kategori_barang === 'bhp' && blank($detail->satuan_perolehan)) {
                $detail->satuan_perolehan = self::SATUAN_PEROLEHAN_PCS;
            }

            if ($detail->kategori_barang === 'bhp') {
                $detail->jenis_bhp = $detail->jenis_bhp ?: BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR;
                $detail->jenis_aset = null;
            }

            if ($detail->kategori_barang === 'aset') {
                $detail->jenis_bhp = null;
            }

            $detail->harga_satuan = (int) ($detail->harga_satuan ?: $detail->harga_perolehan);
            $detail->total_harga = (int) ($detail->total_harga ?: ($detail->jumlah_perolehan * $detail->harga_satuan));
            $detail->persentase_subtotal = (float) ($detail->persentase_subtotal ?? 0);
            $detail->alokasi_diskon = (int) ($detail->alokasi_diskon ?? 0);
            $detail->alokasi_biaya_lainnya = (int) ($detail->alokasi_biaya_lainnya ?? 0);
            $detail->harga_perolehan = (int) ($detail->harga_perolehan ?? 0);
            $detail->total_harga_perolehan = (int) ($detail->total_harga_perolehan ?? 0);
        });

        static::created(function (PerolehanBarangDetail $detail): void {
            if ($detail->kategori_barang === 'aset') {
                $lastKode = BarangKantor::where('kategori_barang', 'aset')->orderByDesc('kode_barang')->value('kode_barang');
                $next = $lastKode ? intval(substr($lastKode, 5)) + 1 : 1;
                $hargaSatuanAset = self::distributeUnitPrices((int) $detail->total_harga_perolehan, (int) $detail->jumlah_perolehan);

                for ($i = 0; $i < $detail->jumlah_perolehan; $i++) {
                    $asset = BarangKantor::create([
                        'kategori_barang' => 'aset',
                        'kode_barang' => 'ASET-' . str_pad($next++, 5, '0', STR_PAD_LEFT),
                        'nama_barang' => $detail->nama_barang,
                        'status_penggunaan' => $detail->statusSiapPakaiAset(),
                        'tanggal_diterima' => $detail->tanggalSiapPakaiAset(),
                        'harga_perolehan' => $hargaSatuanAset[$i] ?? $detail->harga_perolehan,
                        'jenis_aset' => $detail->jenis_aset,
                        'kategori_aset_id' => $detail->kategori_aset_id,
                        'umur_ekonomis' => $detail->umur_ekonomis,
                        'nilai_residu' => $detail->nilai_residu,
                        'stok' => 1,
                        'keterangan' => $detail->perolehanBarang?->keterangan,
                        'status_barang' => 'Aktif',
                        'perolehan_barang_detail_id' => $detail->getKey(),
                    ]);

                    if ((int) $asset->perolehan_barang_detail_id !== (int) $detail->getKey()) {
                        $asset->updateQuietly(['perolehan_barang_detail_id' => $detail->getKey()]);
                    }
                }
            }

            if ($detail->kategori_barang === 'bhp') {
                $stokMasuk = self::convertToPcs((int) $detail->jumlah_perolehan, $detail->satuan_perolehan);
                $satuanBarangKantor = self::resolveBarangKantorSatuan($detail->satuan_perolehan);
                $bk = BarangKantor::where('kategori_barang', 'bhp')->where('nama_barang', $detail->nama_barang)->first();

                if ($bk) {
                    $bk->update([
                        'satuan' => $satuanBarangKantor,
                        'jenis_bhp' => $detail->jenis_bhp ?: $bk->jenis_bhp,
                    ]);
                    $bk->increment('stok', $stokMasuk);
                    $detail->updateQuietly(['kode_barang' => $bk->kode_barang]);
                } else {
                    $newBk = BarangKantor::create([
                        'kategori_barang' => 'bhp',
                        'kode_barang' => self::nextBarangCode('bhp'),
                        'nama_barang' => $detail->nama_barang,
                        'jenis_bhp' => $detail->jenis_bhp,
                        'stok' => $stokMasuk,
                        'satuan' => $satuanBarangKantor,
                        'keterangan' => $detail->perolehanBarang?->keterangan,
                        'status_barang' => 'Aktif',
                    ]);

                    $detail->updateQuietly(['kode_barang' => $newBk->kode_barang]);
                }
            }
        });

        static::updated(function (PerolehanBarangDetail $detail): void {
            if ($detail->kategori_barang === 'bhp') {
                $stokBaru = self::convertToPcs((int) $detail->jumlah_perolehan, $detail->satuan_perolehan);
                $stokLama = self::convertToPcs(
                    (int) $detail->getOriginal('jumlah_perolehan'),
                    $detail->getOriginal('satuan_perolehan') ?: self::SATUAN_PEROLEHAN_PCS,
                );
                $selisih = $stokBaru - $stokLama;
                $bk = BarangKantor::find($detail->kode_barang)
                    ?? BarangKantor::where('kategori_barang', 'bhp')->where('nama_barang', $detail->getOriginal('nama_barang'))->first();

                if ($bk) {
                    $bk->update([
                        'nama_barang' => $detail->nama_barang,
                        'jenis_bhp' => $detail->jenis_bhp ?: $bk->jenis_bhp,
                        'satuan' => self::resolveBarangKantorSatuan($detail->satuan_perolehan),
                    ]);

                    if ($selisih !== 0) {
                        $bk->increment('stok', $selisih);
                    }
                }
            }

            if ($detail->kategori_barang === 'aset') {
                $desiredCount = max(1, (int) $detail->jumlah_perolehan);
                $assets = $detail->asetItems()->orderBy('kode_barang')->get();
                $currentCount = $assets->count();

                if ($currentCount < $desiredCount) {
                    $lastKode = BarangKantor::where('kategori_barang', 'aset')->orderByDesc('kode_barang')->value('kode_barang');
                    $next = $lastKode ? intval(substr($lastKode, 5)) + 1 : 1;

                    for ($i = $currentCount; $i < $desiredCount; $i++) {
                        $asset = BarangKantor::create([
                            'kategori_barang' => 'aset',
                            'kode_barang' => 'ASET-' . str_pad($next++, 5, '0', STR_PAD_LEFT),
                            'nama_barang' => $detail->nama_barang,
                            'status_penggunaan' => $detail->statusSiapPakaiAset(),
                            'tanggal_diterima' => $detail->tanggalSiapPakaiAset(),
                            'harga_perolehan' => $detail->harga_perolehan,
                            'jenis_aset' => $detail->jenis_aset,
                            'kategori_aset_id' => $detail->kategori_aset_id,
                            'umur_ekonomis' => $detail->umur_ekonomis,
                            'nilai_residu' => $detail->nilai_residu,
                            'stok' => 1,
                            'keterangan' => $detail->perolehanBarang?->keterangan,
                            'status_barang' => 'Aktif',
                            'perolehan_barang_detail_id' => $detail->getKey(),
                        ]);

                        if ((int) $asset->perolehan_barang_detail_id !== (int) $detail->getKey()) {
                            $asset->updateQuietly(['perolehan_barang_detail_id' => $detail->getKey()]);
                        }
                    }
                } elseif ($currentCount > $desiredCount) {
                    $assets
                        ->slice($desiredCount)
                        ->each(function (BarangKantor $asset): void {
                            $asset->penyusutans()->get()->each->delete();
                            $asset->delete();
                        });
                }

                $assets = $detail->asetItems()->orderBy('kode_barang')->get();
                $hargaSatuanAset = self::distributeUnitPrices((int) $detail->total_harga_perolehan, max($assets->count(), 1));

                foreach ($assets as $index => $asset) {
                    $asset->update([
                        'nama_barang' => $detail->nama_barang,
                        'status_penggunaan' => $detail->statusSiapPakaiAset(),
                        'tanggal_diterima' => $detail->tanggalSiapPakaiAset(),
                        'harga_perolehan' => $hargaSatuanAset[$index] ?? $detail->harga_perolehan,
                        'jenis_aset' => $detail->jenis_aset,
                        'kategori_aset_id' => $detail->kategori_aset_id,
                        'umur_ekonomis' => $detail->umur_ekonomis,
                        'nilai_residu' => $detail->nilai_residu,
                        'keterangan' => $detail->perolehanBarang?->keterangan,
                    ]);
                }
            }
        });

        static::deleting(function (PerolehanBarangDetail $detail): void {
            PerolehanBarangDeletionService::cleanupDetail($detail);
        });
    }

    private static function distributeUnitPrices(int $total, int $jumlah): array
    {
        $jumlah = max(1, $jumlah);
        $base = intdiv($total, $jumlah);
        $remainder = $total - ($base * $jumlah);
        $prices = array_fill(0, $jumlah, $base);

        for ($i = 0; $i < $remainder; $i++) {
            $prices[$i]++;
        }

        return $prices;
    }

    private static function nextBarangCode(string $kategori): string
    {
        $prefix = $kategori === 'aset' ? 'ASET' : 'BHP';
        $lastKode = BarangKantor::where('kategori_barang', $kategori)
            ->where('kode_barang', 'like', $prefix . '-%')
            ->orderByDesc('kode_barang')
            ->value('kode_barang');

        $next = $lastKode ? ((int) substr($lastKode, strlen($prefix) + 1)) + 1 : 1;

        return $prefix . '-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function tanggalSiapPakaiAset(): mixed
    {
        return $this->statusSiapPakaiAset() === BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN
            ? null
            : $this->perolehanBarang?->tanggal_diterima;
    }

    public function statusSiapPakaiAset(): string
    {
        return $this->perolehanBarang?->status_penggunaan ?: BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN;
    }
}
