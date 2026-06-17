<?php

namespace App\Filament\Admin\Resources\BarangKantors\Pages;

use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBarangKantors extends ListRecords
{
    protected static string $resource = BarangKantorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Barang Kantor'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Resources\BarangKantors\Widgets\AsetTetapTable::class,
            \App\Filament\Admin\Resources\BarangKantors\Widgets\BhpGroupWidget::class,
        ];
    }
}
