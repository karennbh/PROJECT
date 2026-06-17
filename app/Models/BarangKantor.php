<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class BarangKantor extends Model
{
    use HasFactory;

    public const STATUS_AKTIF = 'Aktif';
    public const STATUS_TIDAK_AKTIF = 'Tidak Aktif';
    public const STATUS_BELUM_SIAP_DIGUNAKAN = 'belum_siap_digunakan';
    public const STATUS_SIAP_DIGUNAKAN = 'siap_digunakan';
    public const STATUS_PINJAM_TERSEDIA             = 'Tersedia';
    public const STATUS_PINJAM_DIPINJAM             = 'Sedang Dipinjam';
    public const STATUS_PINJAM_DIDISTRIBUSIKAN      = 'Telah Didistribusikan';
    public const STATUS_PINJAM_TIDAK_DIPINJAMKAN    = 'Tidak untuk Dipinjamkan';
    public const JENIS_BHP_ATK_OPERASIONAL_KANTOR = 'atk_operasional_kantor';
    public const JENIS_BHP_INVENTARIS_KANTOR = 'inventaris_kantor';
    public const STATUS_STOK_HABIS = 'Habis';
    public const STATUS_STOK_MENIPIS = 'Menipis';
    public const STATUS_STOK_TERSEDIA = 'Tersedia';

    protected $table = 'barang_kantors';
    protected $primaryKey = 'kode_barang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kategori_barang',
        'kode_barang',
        'barcode',          
        'nama_barang',
        'stok',
        'satuan',
        'jenis_aset',
        'jenis_bhp',
        'kategori_aset_id',
        'umur_ekonomis',
        'nilai_residu',
        'status_penggunaan',
        'tanggal_diterima',
        'harga_perolehan',
        'keterangan',
        'status_barang',
        'status_pinjam',
        'perolehan_barang_detail_id',
        'foto',
    ];

    protected $casts = [
        'stok' => 'integer',
        'umur_ekonomis' => 'integer',
        'nilai_residu' => 'integer',
        'tanggal_diterima' => 'date',
        'harga_perolehan' => 'integer',
    ];

    public function kategoriAset()
    {
        return $this->belongsTo(KategoriAsetTetap::class, 'kategori_aset_id', 'id_kategori_aset');
    }

    public function penyusutans()
    {
        return $this->hasMany(PenyusutanAsetTetap::class, 'kode_barang', 'kode_barang');
    }

    public function perolehanBarangDetail()
    {
        return $this->belongsTo(PerolehanBarangDetail::class, 'perolehan_barang_detail_id');
    }

    public function tanggalPembelianPerolehan(): ?Carbon
    {
        $tanggal = $this->perolehanBarangDetail?->perolehanBarang?->tanggal_pembelian
            ?? $this->perolehanBarangDetail()
                ->with('perolehanBarang')
                ->first()
                ?->perolehanBarang
                ?->tanggal_pembelian;

        return $tanggal ? Carbon::parse($tanggal)->startOfDay() : null;
    }

    public function getBarcodeTargetUrlAttribute(): string
    {
        if (Route::has('barang.public-detail')) {
            $routePath = route('barang.public-detail', ['kodeBarang' => $this->kode_barang], false);
        } elseif (Route::has('barang.detail')) {
            $routePath = route('barang.detail', ['kodeBarang' => $this->kode_barang], false);
        } else {
            $routePath = '/public/barang-kantor/' . rawurlencode($this->kode_barang);
        }

        return rtrim($this->resolveBarcodeBaseUrl(), '/') . '/' . ltrim($routePath, '/');
    }

    private function resolveBarcodeBaseUrl(): string
    {
        $configuredUrl = trim((string) config('app.barcode_base_url'));

        if ($configuredUrl !== '' && strtolower($configuredUrl) !== 'auto') {
            return $configuredUrl;
        }

        if (! app()->runningInConsole()) {
            $request = request();
            $requestBaseUrl = $request->getSchemeAndHttpHost();
            $appUrl = rtrim((string) config('app.url'), '/');
            $appHost = parse_url($appUrl, PHP_URL_HOST);

            if (! $this->isLoopbackHost($request->getHost())) {
                if ($appHost && strcasecmp($appHost, $request->getHost()) === 0) {
                    return $appUrl;
                }

                return $requestBaseUrl;
            }

            if ($localNetworkBaseUrl = $this->makeLocalNetworkBaseUrl($request->getScheme(), $request->getPort())) {
                return $localNetworkBaseUrl;
            }

            return $requestBaseUrl;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $appHost = parse_url($appUrl, PHP_URL_HOST);

        if ($appHost && ! $this->isLoopbackHost($appHost)) {
            return $appUrl;
        }

        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $port = (int) (parse_url($appUrl, PHP_URL_PORT) ?: env('SERVER_PORT', 8000));

        return $this->makeLocalNetworkBaseUrl($scheme, $port) ?: $appUrl;
    }

    private function makeLocalNetworkBaseUrl(string $scheme, ?int $port): ?string
    {
        $configuredIp = trim((string) config('app.barcode_local_ip'));
        $ipAddress = $configuredIp !== '' && strtolower($configuredIp) !== 'auto' && $this->isUsableIpv4($configuredIp)
            ? $configuredIp
            : $this->detectLocalNetworkIp();

        if (! $ipAddress) {
            return null;
        }

        $portSuffix = $port && ! in_array($port, [80, 443], true) ? ':' . $port : '';

        return $scheme . '://' . $ipAddress . $portSuffix;
    }

    private function detectLocalNetworkIp(): ?string
    {
        $socket = @stream_socket_client('udp://8.8.8.8:80', $errno, $errstr, 1, STREAM_CLIENT_CONNECT);

        if (is_resource($socket)) {
            $socketName = stream_socket_get_name($socket, false);
            fclose($socket);

            $ipAddress = $socketName ? strtok($socketName, ':') : false;

            if ($ipAddress && $this->isUsableIpv4($ipAddress)) {
                return $ipAddress;
            }
        }

        $hostIpAddress = gethostbyname(gethostname());

        return $this->isUsableIpv4($hostIpAddress) ? $hostIpAddress : null;
    }

    private function isLoopbackHost(?string $host): bool
    {
        $host = strtolower((string) $host);

        return in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true);
    }

    private function isUsableIpv4(?string $ipAddress): bool
    {
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        return ! str_starts_with($ipAddress, '127.')
            && ! str_starts_with($ipAddress, '169.254.')
            && $ipAddress !== '0.0.0.0';
    }

    public function getBarcodeQrImageUrlAttribute(): string
    {
        return 'https://quickchart.io/qr?text=' . urlencode($this->barcode_target_url) . '&size=220&margin=1';
    }

    public function isAvailableToBorrow(int $jumlah = 1): bool
    {
        if ($this->status_barang !== self::STATUS_AKTIF || (int) $this->stok < $jumlah) {
            return false;
        }

        if ($this->kategori_barang === 'aset') {
            return $this->isSiapPakai()
                && $this->status_pinjam === self::STATUS_PINJAM_TERSEDIA;
        }

        // BPP Inventaris: tidak bisa dipinjam jika sudah didistribusikan atau sedang dipinjam
        if ($this->kategori_barang === 'bhp' && $this->jenis_bhp === self::JENIS_BHP_INVENTARIS_KANTOR) {
            return $this->status_pinjam !== self::STATUS_PINJAM_DIDISTRIBUSIKAN
                && $this->status_pinjam !== self::STATUS_PINJAM_DIPINJAM
                && $this->status_pinjam !== self::STATUS_PINJAM_TIDAK_DIPINJAMKAN;
        }

        return false;
    }

    public function markAsBorrowed(int $jumlah = 1): void
    {
        // BPP Inventaris Kantor: stok tidak dikurangi, hanya status_pinjam yang berubah
        if ($this->kategori_barang === 'bhp' && $this->jenis_bhp === self::JENIS_BHP_INVENTARIS_KANTOR) {
            $this->update([
                'status_pinjam' => self::STATUS_PINJAM_DIPINJAM,
            ]);
            return;
        }

        // Aset & ATK: kurangi stok seperti biasa
        $stokTersisa = max(0, ((int) $this->stok) - $jumlah);

        $this->update([
            'stok' => $stokTersisa,
            'status_pinjam' => $this->kategori_barang === 'aset' || $stokTersisa <= 0
                ? self::STATUS_PINJAM_DIPINJAM
                : self::STATUS_PINJAM_TERSEDIA,
        ]);
    }

    public function markAsReturned(int $jumlah = 1): void
    {
        // BPP Inventaris Kantor: stok tidak ditambah, hanya status_pinjam yang dikembalikan
        if ($this->kategori_barang === 'bhp' && $this->jenis_bhp === self::JENIS_BHP_INVENTARIS_KANTOR) {
            $this->update([
                'status_pinjam' => $this->status_barang === self::STATUS_AKTIF
                    ? self::STATUS_PINJAM_TERSEDIA
                    : null,
            ]);
            return;
        }

        // Aset & ATK: kembalikan stok seperti biasa
        $this->update([
            'stok' => ((int) $this->stok) + $jumlah,
            'status_pinjam' => $this->status_barang === self::STATUS_AKTIF
                ? self::STATUS_PINJAM_TERSEDIA
                : null,
        ]);
    }

    public function getKategoriBarangLabelAttribute(): string
    {
        return $this->kategori_barang === 'aset'
            ? 'Aset Tetap'
            : 'Barang Habis Pakai';
    }

    public function getJenisBarangLabelAttribute(): string
    {
        if ($this->kategori_barang === 'aset') {
            return $this->jenis_aset_label;
        }

        return match ($this->jenis_bhp) {
            self::JENIS_BHP_ATK_OPERASIONAL_KANTOR => 'ATK Operasional Kantor',
            self::JENIS_BHP_INVENTARIS_KANTOR => 'BPP Inventaris Kantor',
            default => 'Barang Habis Pakai',
        };
    }

    public function getJenisAsetLabelAttribute(): string
    {
        return match ($this->jenis_aset) {
            'sarana_pendidikan_laboratorium' => 'Sarana Pendidikan Laboratorium',
            'inventaris_kantor' => 'Inventaris Kantor',
            'kendaraan' => 'Kendaraan',
            default => 'Aset Tetap',
        };
    }

    public function getStatusStokBhpAttribute(): string
    {
        if ((int) $this->stok <= 0) {
            return self::STATUS_STOK_HABIS;
        }

        return $this->isBhpStockMenipis()
            ? self::STATUS_STOK_MENIPIS
            : self::STATUS_STOK_TERSEDIA;
    }

    public function isBhpStockMenipis(): bool
    {
        // BPP Inventaris Kantor tidak mengenal status menipis — langsung tersedia atau habis
        if ($this->jenis_bhp === self::JENIS_BHP_INVENTARIS_KANTOR) {
            return false;
        }

        return match (strtolower((string) $this->satuan)) {
            'pcs' => (int) $this->stok <= 3,
            'kotak' => (int) $this->stok <= 2,
            default => false,
        };
    }

    public function scopeBhpStokHabis(Builder $query): Builder
    {
        return $query
            ->where('kategori_barang', 'bhp')
            ->where('stok', '<=', 0);
    }

    public function scopeBhpStokMenipis(Builder $query): Builder
    {
        return $query
            ->where('kategori_barang', 'bhp')
            ->where('stok', '>', 0)
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('satuan', 'Pcs')
                        ->where('stok', '<=', 3);
                })->orWhere(function (Builder $query): void {
                    $query->where('satuan', 'Kotak')
                        ->where('stok', '<=', 2);
                });
            });
    }

    public function scopeBhpStokTersedia(Builder $query): Builder
    {
        return $query
            ->where('kategori_barang', 'bhp')
            ->where('stok', '>', 0)
            ->where(function (Builder $query): void {
                $query->whereNull('satuan')
                    ->orWhereNotIn('satuan', ['Pcs', 'Kotak'])
                    ->orWhere(function (Builder $query): void {
                        $query->where('satuan', 'Pcs')
                            ->where('stok', '>', 3);
                    })
                    ->orWhere(function (Builder $query): void {
                        $query->where('satuan', 'Kotak')
                            ->where('stok', '>', 2);
                    });
            });
    }

    public function scopeBorrowableForPeminjaman($query)
    {
        return $query->where(function ($query) {
            $query->where('kategori_barang', 'aset')
                ->orWhere(function ($query) {
                    $query->where('kategori_barang', 'bhp')
                        ->where('jenis_bhp', self::JENIS_BHP_INVENTARIS_KANTOR);
                });
        });
    }

    public function syncAssetStatuses(): void
    {
        if ($this->kategori_barang !== 'aset') {
            return;
        }

        $statusPenyusutan = $this->status_barang === self::STATUS_AKTIF && $this->isSiapPakai()
            ? self::STATUS_AKTIF
            : self::STATUS_TIDAK_AKTIF;

        $this->penyusutans()->update([
            'status_penyusutan' => $statusPenyusutan,
            'status_penggunaan' => $this->status_penggunaan ?: self::STATUS_BELUM_SIAP_DIGUNAKAN,
        ]);
    }

    public function isSiapPakai(): bool
    {
        return in_array($this->status_penggunaan, [
            self::STATUS_SIAP_DIGUNAKAN,
        ], true) && filled($this->tanggal_diterima);
    }

    public function syncPenyusutanData(): void
    {
        if ($this->kategori_barang !== 'aset') {
            return;
        }

        $this->penyusutans()->update([
            'kode_barang' => $this->kode_barang,
            'nama_aset' => $this->nama_barang,
            'status_penggunaan' => $this->status_penggunaan ?: self::STATUS_BELUM_SIAP_DIGUNAKAN,
            'tanggal_diterima' => $this->tanggal_diterima,
            'harga_perolehan' => $this->harga_perolehan,
            'nilai_residu' => $this->nilai_residu ?? 0,
            'umur_ekonomis_tahun' => $this->umur_ekonomis,
            'status_penyusutan' => $this->status_barang === self::STATUS_AKTIF && $this->isSiapPakai()
                ? self::STATUS_AKTIF
                : self::STATUS_TIDAK_AKTIF,
        ]);

        $this->penyusutans->each->syncKeteranganKelengkapan();
    }

    protected static function booted(): void
    {
        static::saving(function (self $barang) {
            $barang->validateNonNegativeNumbers();

            // Guard: status "Telah Didistribusikan" tidak dapat diubah kembali
            if (
                $barang->exists
                && $barang->getOriginal('status_pinjam') === self::STATUS_PINJAM_DIDISTRIBUSIKAN
                && $barang->status_pinjam !== self::STATUS_PINJAM_DIDISTRIBUSIKAN
            ) {
                throw ValidationException::withMessages([
                    'status_pinjam' => 'Status "Telah Didistribusikan" tidak dapat diubah kembali.',
                ]);
            }

            // Guard: status "Tidak untuk Dipinjamkan" tidak dapat diubah kembali
            if (
                $barang->exists
                && $barang->getOriginal('status_pinjam') === self::STATUS_PINJAM_TIDAK_DIPINJAMKAN
                && $barang->status_pinjam !== self::STATUS_PINJAM_TIDAK_DIPINJAMKAN
            ) {
                throw ValidationException::withMessages([
                    'status_pinjam' => 'Status "Tidak untuk Dipinjamkan" tidak dapat diubah kembali.',
                ]);
            }

            if ($barang->kategori_barang === 'bhp') {
                $barang->jenis_aset = null;
                $barang->status_penggunaan = null;
                $barang->tanggal_diterima = null;
                $barang->jenis_bhp = $barang->jenis_bhp ?: self::JENIS_BHP_ATK_OPERASIONAL_KANTOR;

                return;
            }

            $barang->jenis_bhp = null;
            $barang->status_penggunaan = $barang->status_penggunaan
                ?: ($barang->perolehan_barang_detail_id ? self::STATUS_BELUM_SIAP_DIGUNAKAN : self::STATUS_SIAP_DIGUNAKAN);

            if (! $barang->isSiapPakai()) {
                $barang->tanggal_diterima = null;
            }

            $tanggalPembelian = $barang->tanggalPembelianPerolehan();
            if (
                $tanggalPembelian
                && filled($barang->tanggal_diterima)
                && Carbon::parse($barang->tanggal_diterima)->startOfDay()->lt($tanggalPembelian)
            ) {
                throw ValidationException::withMessages([
                    'tanggal_diterima' => 'Tanggal Diterima tidak boleh lebih awal dari tanggal pembelian perolehan (' . $tanggalPembelian->format('d/m/Y') . ').',
                ]);
            }

            if (
                $barang->exists &&
                $barang->getOriginal('status_barang') === self::STATUS_TIDAK_AKTIF &&
                $barang->status_barang === self::STATUS_AKTIF
            ) {
                throw ValidationException::withMessages([
                    'status_barang' => 'Aset yang sudah Tidak Aktif tidak dapat diubah kembali menjadi Aktif.',
                ]);
            }

            if ($barang->status_barang !== self::STATUS_AKTIF) {
                $barang->status_barang = self::STATUS_TIDAK_AKTIF;
                $barang->status_pinjam = null;
                return;
            }

            if (blank($barang->status_pinjam)) {
                $barang->status_pinjam = self::STATUS_PINJAM_TERSEDIA;
            }
        });

        /**
         * Barcode dibuat dari kode barang setelah record tersimpan.
         */
        static::created(function (self $barang) {

            if (empty($barang->barcode)) {
                $barang->barcode = $barang->kode_barang;
                $barang->saveQuietly();
            }

            if ($barang->kategori_barang === 'aset') {
                \App\Models\PenyusutanAsetTetap::create([
                    'kode_barang'           => $barang->kode_barang,
                    'nama_aset'             => $barang->nama_barang,
                    'status_penggunaan'      => $barang->status_penggunaan ?: self::STATUS_BELUM_SIAP_DIGUNAKAN,
                    'tanggal_diterima' => $barang->tanggal_diterima,
                    'harga_perolehan'        => $barang->harga_perolehan,
                    'nilai_residu'           => $barang->nilai_residu ?? 0,
                    'umur_ekonomis_tahun'    => $barang->umur_ekonomis,
                    'status_penyusutan'      => $barang->status_barang === self::STATUS_AKTIF && $barang->isSiapPakai()
                        ? self::STATUS_AKTIF
                        : self::STATUS_TIDAK_AKTIF,
                ]);
            }
        });

        static::saved(function (self $barang) {
            $barang->syncAssetStatuses();
            $barang->syncPenyusutanData();
        });

        static::deleting(function (self $barang): void {
            if ($barang->kategori_barang === 'aset') {
                $barang->penyusutans()->get()->each->delete();
            }
        });
    }

    private function validateNonNegativeNumbers(): void
    {
        $labels = [
            'stok' => 'Stok',
            'umur_ekonomis' => 'Umur ekonomis',
            'nilai_residu' => 'Nilai residu',
            'harga_perolehan' => 'Harga perolehan',
        ];

        $errors = [];

        foreach ($labels as $field => $label) {
            if ($this->{$field} !== null && (int) $this->{$field} < 0) {
                $errors["data.{$field}"] = "{$label} tidak boleh kurang dari 0.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
