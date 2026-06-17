<?php

namespace App\Filament\Admin\Resources\BarangKantors\Pages;

use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateBarangKantor extends CreateRecord
{
    protected static string $resource = BarangKantorResource::class;
    protected ?bool $hasUnsavedDataChangesAlert = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Barang Kantor berhasil ditambahkan';
    }
    public function getTitle(): string
    {
        return 'Tambah Barang Kantor';
    }

    public function getCreateButtonLabel(): string
    {
        return 'Tambah Barang Kantor';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Barang Kantor';
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Batal')
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');
    }

    // tidak ada afterCreate lagi → penyusutan full manual
}
