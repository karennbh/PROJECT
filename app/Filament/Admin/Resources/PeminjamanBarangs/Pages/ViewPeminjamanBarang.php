<?php

namespace App\Filament\Admin\Resources\PeminjamanBarangs\Pages;

use App\Filament\Admin\Resources\PeminjamanBarangs\PeminjamanBarangResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPeminjamanBarang extends ViewRecord
{
    protected static string $resource = PeminjamanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        $record = $this->getRecord();

        return 'Peminjaman Barang Kantor - ' . ($record->nama_barang ?? $record->barang?->nama_barang ?? $record->kode_barang ?? $record->id_peminjaman);
    }
}
