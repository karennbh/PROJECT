<?php

namespace App\Filament\Admin\Resources\BarangKantors\Pages;

use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use App\Models\BarangKantor;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditBarangKantor extends EditRecord
{
    protected static string $resource = BarangKantorResource::class;

    public function getTitle(): string
    {
        return 'Edit ' . ($this->record->nama_barang ?? 'Barang Kantor');
    }

    public function getBreadcrumb(): string
    {
        return 'Edit';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record instanceof BarangKantor && $this->isStatusPenggunaanLocked($this->record)) {
            $data['status_penggunaan'] = $this->record->status_penggunaan;
            $data['tanggal_diterima'] = $this->record->tanggal_diterima?->toDateString();

            return $data;
        }

        $tanggalPembelian = $this->record instanceof BarangKantor
            ? $this->record->tanggalPembelianPerolehan()
            : null;

        if (
            $tanggalPembelian
            && ($data['status_penggunaan'] ?? null) === BarangKantor::STATUS_SIAP_DIGUNAKAN
            && filled($data['tanggal_diterima'] ?? null)
            && Carbon::parse($data['tanggal_diterima'])->startOfDay()->lt($tanggalPembelian)
        ) {
            throw ValidationException::withMessages([
                'data.tanggal_diterima' => 'Tanggal Diterima tidak boleh lebih awal dari tanggal pembelian perolehan (' . $tanggalPembelian->format('d/m/Y') . ').',
            ]);
        }

        return $data;
    }

    private function isStatusPenggunaanLocked(BarangKantor $record): bool
    {
        return $record->status_penggunaan === BarangKantor::STATUS_SIAP_DIGUNAKAN
            && filled($record->tanggal_diterima);
    }
}
