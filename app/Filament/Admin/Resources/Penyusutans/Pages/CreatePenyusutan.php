<?php

namespace App\Filament\Admin\Resources\Penyusutans\Pages;

use App\Filament\Admin\Resources\Penyusutans\PenyusutanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenyusutan extends CreateRecord
{
    protected static string $resource = PenyusutanResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function getTitle(): string
    {
        return 'Tambah Penyusutan Aset Tetap';
    }

    public function getCreateButtonLabel(): string
    {
        return 'Tambah Penyusutan Aset Tetap';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah Penyusutan Aset Tetap';
    }
}
