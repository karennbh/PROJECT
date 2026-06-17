<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps\Pages;

use App\Filament\Admin\Resources\KategoriAsetTetaps\KategoriAsetTetapResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Models\KategoriAsetTetap;

class ListKategoriAsetTetaps extends ListRecords
{
    protected static string $resource = KategoriAsetTetapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kategori Aset Tetap')
                ->visible(fn () => KategoriAsetTetap::count() < 4),
        ];
    }
}
