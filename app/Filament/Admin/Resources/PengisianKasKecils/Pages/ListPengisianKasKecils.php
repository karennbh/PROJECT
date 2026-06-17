<?php

namespace App\Filament\Admin\Resources\PengisianKasKecils\Pages;

use App\Filament\Admin\Resources\PengisianKasKecils\PengisianKasKecilResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengisianKasKecils extends ListRecords
{
    protected static string $resource = PengisianKasKecilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Pengisian Kas Kecil'),
        ];
    }
}
