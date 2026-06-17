<?php

namespace App\Filament\Admin\Resources\COAS\Pages;

use App\Filament\Admin\Resources\COAS\COAResource;
use App\Support\JurnalAutoSyncService;
use Filament\Resources\Pages\CreateRecord;

class CreateCOA extends CreateRecord
{
    protected static string $resource = COAResource::class;

    protected function afterCreate(): void
    {
        JurnalAutoSyncService::syncAll();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string
    {
        return 'Tambah COA';
    }

    public function getCreateButtonLabel(): string
    {
        return 'Tambah COA';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah COA';
    }
}