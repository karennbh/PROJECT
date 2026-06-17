<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs\Pages;

use App\Filament\Admin\Resources\PerolehanBarangs\PerolehanBarangResource;
use App\Models\BarangKantor;
use App\Models\PendapatanHibah;
use App\Models\PerolehanBarang;
use App\Support\KasKecilBalance;
use App\Support\PerolehanBarangAllocator;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class CreatePerolehanBarang extends CreateRecord
{
    protected static string $resource = PerolehanBarangResource::class;
    protected ?bool $hasUnsavedDataChangesAlert = false;

    protected function hasUnsavedDataChangesAlert(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['allocation_last_calculated_signature']);
        $data = $this->normalizeStatusSiapPakai($data);

        if ($this->isHibahSource($data['sumber_perolehan'] ?? PerolehanBarang::SUMBER_PEMBELIAN)) {
            return $this->normalizeHibahData($data);
        }

        $allocated = PerolehanBarangAllocator::allocate($data);
        $this->validateKasKecilBalance((int) ($allocated['grand_total'] ?? 0));
        $allocated['total_nilai_hibah'] = 0;
        $allocated['nilai_pengakuan_pendapatan_hibah_uang'] = 0;

        return $allocated;
    }

    protected function afterCreate(): void
    {
        $this->getRecord()->refresh();
        $this->getRecord()->syncJurnalUmum();
    }

    public function getTitle(): string
    {
        return 'Tambah Perolehan Barang';
    }

    public function getCreateButtonLabel(): string
    {
        return 'Tambah Perolehan Barang';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Perolehan Barang';
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Batal')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');
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

            $sisa = $hibah->sisa;

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
        $available = KasKecilBalance::available();

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

}

