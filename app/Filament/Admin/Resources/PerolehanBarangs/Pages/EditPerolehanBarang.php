<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs\Pages;

use App\Filament\Admin\Resources\PerolehanBarangs\PerolehanBarangResource;
use App\Models\BarangKantor;
use App\Models\PendapatanHibah;
use App\Models\PerolehanBarang;
use App\Models\PerolehanBarangDetail;
use App\Support\KasKecilBalance;
use App\Support\PerolehanBarangAllocator;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditPerolehanBarang extends EditRecord
{
    protected static string $resource = PerolehanBarangResource::class;

    public int $oldJumlah = 0;
    protected array $normalizedDetails = [];

    public function getTitle(): string
    {
        return 'Edit ' . ($this->record->id_perolehan_barang ?? 'Perolehan Barang');
    }

    public function getBreadcrumb(): string
    {
        return 'Edit';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName())
            ->extraAttributes(['novalidate' => 'novalidate'])
            ->footer([
                $this->getFormActionsContentComponent(),
            ]);
    }

    public function mount($record): void
    {
        parent::mount($record);

        $this->oldJumlah = (int) $this->record
            ->details()
            ->sum('jumlah_perolehan');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['sumber_perolehan'] ?? null) === PerolehanBarang::SUMBER_HIBAH) {
            $data['status_penggunaan'] = BarangKantor::STATUS_SIAP_DIGUNAKAN;
            $data['tanggal_diterima'] = $data['tanggal_pembelian'] ?? null;
        }

        $details = $this->record->details()
            ->orderBy('id_perolehan_barang_detail')
            ->get()
            ->map(fn ($detail) => $detail->only([
                'id_perolehan_barang_detail',
                'nama_barang',
                'kategori_barang',
                'jenis_aset',
                'jenis_bhp',
                'kategori_aset_id',
                'umur_ekonomis',
                'nilai_residu',
                'kode_barang',
                'jumlah_perolehan',
                'satuan_perolehan',
                'harga_satuan',
                'total_harga',
                'persentase_subtotal',
                'alokasi_diskon',
                'alokasi_biaya_lainnya',
                'harga_perolehan',
                'total_harga_perolehan',
            ]))
            ->all();

        if ($this->isHibahSource($data['sumber_perolehan'] ?? PerolehanBarang::SUMBER_PEMBELIAN)) {
            $normalized = $this->normalizeHibahData(array_merge($data, ['details' => $details]));
            $data['details'] = $normalized['details'];
            $data['total_nilai_hibah'] = $normalized['total_nilai_hibah'];
            $data['subtotal_barang'] = 0;
            $data['grand_total'] = $normalized['grand_total'];

            return $data;
        }

        $allocated = PerolehanBarangAllocator::allocate(array_merge($data, ['details' => $details]));
        $data['details'] = $allocated['details'];
        $data['subtotal_barang'] = $allocated['subtotal_barang'];
        $data['diskon_total'] = $allocated['diskon_total'] ?? 0;
        $data['biaya_lainnya_total'] = $allocated['biaya_lainnya_total'] ?? 0;
        $data['grand_total'] = $allocated['grand_total'];
        $data['total_nilai_hibah'] = 0;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['allocation_last_calculated_signature']);
        $data = $this->normalizeStatusSiapPakai($data);

        $details = collect($data['details'] ?? [])
            ->whenEmpty(function () {
                return $this->record->details()
                    ->orderBy('id_perolehan_barang_detail')
                    ->get()
                    ->map(fn ($detail) => $detail->only([
                        'id_perolehan_barang_detail',
                        'nama_barang',
                        'kategori_barang',
                        'jenis_aset',
                        'jenis_bhp',
                        'kategori_aset_id',
                        'umur_ekonomis',
                        'nilai_residu',
                        'kode_barang',
                        'jumlah_perolehan',
                        'satuan_perolehan',
                        'harga_satuan',
                        'total_harga',
                        'persentase_subtotal',
                        'alokasi_diskon',
                        'alokasi_biaya_lainnya',
                        'harga_perolehan',
                        'total_harga_perolehan',
                    ]));
            })
            ->values()
            ->all();

        $details = $this->mergeDetailsWithExistingValues($details);

        $this->validateDetailNominals($data, $details);

        if ($this->isHibahSource($data['sumber_perolehan'] ?? PerolehanBarang::SUMBER_PEMBELIAN)) {
            $normalized = $this->normalizeHibahData(array_merge($data, ['details' => $details]));
            $this->normalizedDetails = $normalized['details'];

            return $normalized;
        }

        $allocated = PerolehanBarangAllocator::allocate(array_merge($data, ['details' => $details]));
        $this->validateKasKecilBalance((int) ($allocated['grand_total'] ?? 0));
        $this->normalizedDetails = $allocated['details'];
        $allocated['total_nilai_hibah'] = 0;
        $allocated['nilai_pengakuan_pendapatan_hibah_uang'] = 0;

        return $allocated;
    }

    private function validateDetailNominals(array $data, array $details): void
    {
        $errors = [];
        $isHibah = $this->isHibahSource($data['sumber_perolehan'] ?? PerolehanBarang::SUMBER_PEMBELIAN);

        foreach ($details as $index => $detail) {
            $field = $isHibah ? 'harga_perolehan' : 'harga_satuan';
            $label = $isHibah ? 'Harga perolehan' : 'Harga satuan';
            $value = (int) preg_replace('/[^0-9]/', '', (string) ($detail[$field] ?? ''));

            if ($value <= 0) {
                $errors["data.details.{$index}.{$field}"] = $label . ' harus lebih dari 0.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function mergeDetailsWithExistingValues(array $details): array
    {
        $existingDetails = $this->record->details()
            ->orderBy('id_perolehan_barang_detail')
            ->get()
            ->values();

        return collect($details)
            ->map(function (array $detail, int $index) use ($existingDetails): array {
                $existing = isset($detail['id_perolehan_barang_detail'])
                    ? $existingDetails->firstWhere('id_perolehan_barang_detail', $detail['id_perolehan_barang_detail'])
                    : $existingDetails->get($index);

                if (! $existing instanceof PerolehanBarangDetail) {
                    return $detail;
                }

                foreach ([
                    'id_perolehan_barang_detail',
                    'nama_barang',
                    'kategori_barang',
                    'jenis_aset',
                    'jenis_bhp',
                    'kategori_aset_id',
                    'umur_ekonomis',
                    'nilai_residu',
                    'kode_barang',
                    'satuan_perolehan',
                ] as $field) {
                    if (! array_key_exists($field, $detail) || blank($detail[$field])) {
                        $detail[$field] = $existing->{$field};
                    }
                }

                return $detail;
            })
            ->values()
            ->all();
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($this->normalizedDetails !== []) {
            $existingDetails = $record->details()->orderBy('id_perolehan_barang_detail')->get()->values();

            foreach ($existingDetails as $index => $detail) {
                $allocatedDetail = $this->normalizedDetails[$index] ?? null;

                if (! is_array($allocatedDetail)) {
                    continue;
                }

                $detail->update([
                    'jumlah_perolehan' => (int) ($allocatedDetail['jumlah_perolehan'] ?? $detail->jumlah_perolehan),
                    'satuan_perolehan' => $allocatedDetail['satuan_perolehan'] ?? $detail->satuan_perolehan,
                    'harga_satuan' => (int) ($allocatedDetail['harga_satuan'] ?? $detail->harga_satuan),
                    'total_harga' => (int) ($allocatedDetail['total_harga'] ?? $detail->total_harga),
                    'persentase_subtotal' => (float) ($allocatedDetail['persentase_subtotal'] ?? $detail->persentase_subtotal),
                    'alokasi_diskon' => (int) ($allocatedDetail['alokasi_diskon'] ?? $detail->alokasi_diskon),
                    'alokasi_biaya_lainnya' => (int) ($allocatedDetail['alokasi_biaya_lainnya'] ?? $detail->alokasi_biaya_lainnya),
                    'harga_perolehan' => (int) ($allocatedDetail['harga_perolehan'] ?? $detail->harga_perolehan),
                    'total_harga_perolehan' => (int) ($allocatedDetail['total_harga_perolehan'] ?? $detail->total_harga_perolehan),
                ]);
            }

            $record->refresh();
        }

        foreach ($record->details as $detail) {
            if ($detail->kategori_barang !== 'aset') {
                continue;
            }

            $assets = $detail->asetItems()->get();
            $hargaSatuan = $this->distributeUnitPrices((int) $detail->total_harga_perolehan, max($assets->count(), 1));

            foreach ($assets as $index => $bk) {
                $bk->update([
                    'nama_barang' => $detail->nama_barang,
                    'status_penggunaan' => $record->status_penggunaan ?: BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN,
                    'tanggal_diterima' => ($record->status_penggunaan ?: BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN) === BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN
                        ? null
                        : $record->tanggal_diterima,
                    'harga_perolehan' => $hargaSatuan[$index] ?? $detail->harga_perolehan,
                    'jenis_aset' => $detail->jenis_aset,
                    'kategori_aset_id' => $detail->kategori_aset_id,
                    'umur_ekonomis' => $detail->umur_ekonomis,
                    'nilai_residu' => $detail->nilai_residu,
                    'keterangan' => $record->keterangan,
                ]);
            }
        }

        $record->refresh();
        $record->syncJurnalUmum();
    }

    private function normalizeHibahData(array $data): array
    {
        $details = collect($data['details'] ?? [])
            ->map(function (array $detail): array {
                $jumlah = max(1, (int) ($detail['jumlah_perolehan'] ?? 1));
                $hargaPerolehan = $this->normalizeRupiah($detail['harga_perolehan'] ?? 0);

                $detail['kategori_barang'] = 'aset';
                $detail['kode_barang'] = null;
                $detail['jenis_bhp'] = null;
                $detail['jumlah_perolehan'] = $jumlah;
                $detail['harga_satuan'] = 0;
                $detail['total_harga'] = 0;
                $detail['persentase_subtotal'] = 0;
                $detail['alokasi_diskon'] = 0;
                $detail['alokasi_biaya_lainnya'] = 0;
                $detail['harga_perolehan'] = $hargaPerolehan;
                $detail['total_harga_perolehan'] = $jumlah * $hargaPerolehan;

                return $detail;
            })
            ->values()
            ->all();

        $data['details'] = $details;
        $data['subtotal_barang'] = 0;
        $data['diskon_total'] = 0;
        $data['biaya_lainnya_total'] = 0;
        $totalNilaiHibah = (int) collect($details)->sum('total_harga_perolehan');

        $data['grand_total'] = $totalNilaiHibah;
        $data['foto_nota'] = null;
        $data['total_nilai_hibah'] = $totalNilaiHibah;
        $data['nilai_pengakuan_pendapatan_hibah_uang'] = 0;

        if (($data['sumber_perolehan'] ?? null) === PerolehanBarang::SUMBER_HIBAH_UANG) {
            $hibah = PendapatanHibah::query()->find($data['pendapatan_hibah_id'] ?? null);

            if (! $hibah) {
                throw ValidationException::withMessages([
                    'data.pendapatan_hibah_id' => 'Sumber hibah wajib dipilih.',
                ]);
            }

            $data['nama_pemberi_hibah'] = $hibah->sumber_hibah;
            $data['nilai_pengakuan_pendapatan_hibah_uang'] = (int) $hibah->nilai_hibah;

            $sisa = max(0, (int) $hibah->nilai_hibah - $hibah->usedAmount($this->record?->id_perolehan_barang));

            if ($data['total_nilai_hibah'] > $sisa) {
                throw ValidationException::withMessages([
                    'data.pendapatan_hibah_id' => 'Nilai perolehan aset tidak boleh melebihi sisa dana hibah yang tersedia. Sisa dana hibah saat ini sebesar Rp'
                        . number_format($sisa, 0, ',', '.')
                        . '.',
                ]);
            }
        } else {
            $data['nilai_pengakuan_pendapatan_hibah_uang'] = 0;
            $data['pendapatan_hibah_id'] = null;
        }

        return $data;
    }

    private function isHibahSource(mixed $source): bool
    {
        return in_array($source, [PerolehanBarang::SUMBER_HIBAH_LEGACY, PerolehanBarang::SUMBER_HIBAH, PerolehanBarang::SUMBER_HIBAH_UANG], true);
    }

    private function normalizeStatusSiapPakai(array $data): array
    {
        if (($data['sumber_perolehan'] ?? null) === PerolehanBarang::SUMBER_HIBAH) {
            $data['status_penggunaan'] = BarangKantor::STATUS_SIAP_DIGUNAKAN;
            $data['tanggal_diterima'] = $data['tanggal_pembelian'] ?? null;

            return $data;
        }

        $data['status_penggunaan'] = $data['status_penggunaan'] ?? BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN;

        if ($data['status_penggunaan'] === BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN) {
            $data['tanggal_diterima'] = null;
        }

        return $data;
    }

    private function normalizeRupiah(mixed $state): int
    {
        if ($state === null || $state === '') {
            return 0;
        }

        $numeric = preg_replace('/[^0-9]/', '', (string) $state);

        return (int) ($numeric ?: 0);
    }

    private function validateKasKecilBalance(int $needed): void
    {
        $available = KasKecilBalance::available($this->record?->id_perolehan_barang);

        if ($needed > $available) {
            throw ValidationException::withMessages([
                'data.grand_total' => 'Saldo Kas Kecil tidak mencukupi. Silakan melakukan Pengisian Kas Kecil terlebih dahulu. Sisa saldo: Rp'
                    . number_format($available, 0, ',', '.')
                    . ', nilai perolehan: Rp'
                    . number_format($needed, 0, ',', '.')
                    . '.',
            ]);
        }
    }

    protected function handleRecordDeletion(Model $record): void
    {
        $record->delete();
    }

    private function distributeUnitPrices(int $total, int $jumlah): array
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

}

