<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps\Pages;

use App\Filament\Admin\Resources\KategoriAsetTetaps\KategoriAsetTetapResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\KategoriAsetTetap;
use Filament\Notifications\Notification;


class CreateKategoriAsetTetap extends CreateRecord
{
    protected static string $resource = KategoriAsetTetapResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return 'Tambah Kategori Aset Tetap';
    }
    public function mount(): void
    {
        parent::mount();

        if (KategoriAsetTetap::count() >= 4) {
            Notification::make()
                ->title('Batas Maksimal Kategori Aset Tetap')
                ->body('Kategori aset tetap sudah mencapai 4 kelompok dan tidak dapat ditambah lagi.')
                ->danger()
                ->send();

            redirect($this->getResource()::getUrl('index'));
        }
    }

    public function getCreateButtonLabel(): string
    {
        return 'Tambah Kategori Aset Tetap';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Kategori Aset Tetap';
    }
}
