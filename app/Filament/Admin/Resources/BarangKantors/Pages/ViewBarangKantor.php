<?php

namespace App\Filament\Admin\Resources\BarangKantors\Pages;

use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBarangKantor extends ViewRecord
{
    protected static string $resource = BarangKantorResource::class;

    public function getView(): string
    {
        return 'filament.admin.resources.barang-kantors.detail';
    }

    public function getTitle(): string
    {
        return 'Detail Barang - ' . ($this->record?->nama_barang ?? '-');
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
