<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps\Pages;

use App\Filament\Admin\Resources\KategoriAsetTetaps\KategoriAsetTetapResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKategoriAsetTetap extends EditRecord
{
    protected static string $resource = KategoriAsetTetapResource::class;

    public function getTitle(): string
    {
        return 'Edit ' . ($this->record->nama_kategori_aset ?? 'Kategori Aset Tetap');
    }

    public function getBreadcrumb(): string
    {
        return 'Edit';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat')
                ->color('primary')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // setelah save langsung balik ke list
        return $this->getResource()::getUrl('index');
    }
}
