<?php

namespace App\Filament\Admin\Resources\PeminjamanBarangs\Pages;

use App\Filament\Admin\Resources\PeminjamanBarangs\PeminjamanBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPeminjamanBarang extends EditRecord
{
    protected static string $resource = PeminjamanBarangResource::class;

    public function getTitle(): string
    {
        return 'Edit Peminjaman Barang Kantor';
    }

    public function getBreadcrumb(): string
    {
        return 'Edit';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
