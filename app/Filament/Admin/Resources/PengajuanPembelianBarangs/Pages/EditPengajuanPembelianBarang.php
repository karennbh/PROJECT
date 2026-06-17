<?php

namespace App\Filament\Admin\Resources\PengajuanPembelianBarangs\Pages;

use App\Filament\Admin\Resources\PengajuanPembelianBarangs\PengajuanPembelianBarangResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanPembelianBarang extends EditRecord
{
    protected static string $resource = PengajuanPembelianBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
