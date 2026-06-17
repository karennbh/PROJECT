<?php

namespace App\Filament\Admin\Resources\COAS\Pages;

use App\Filament\Admin\Resources\COAS\COAResource;
use App\Support\JurnalAutoSyncService;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCOA extends EditRecord
{
    protected static string $resource = COAResource::class;

    protected function afterSave(): void
    {
        JurnalAutoSyncService::syncAll();
    }

    public function getTitle(): string
    {
        return 'Edit ' . ($this->record->nama_akun ?? 'COA');
    }

    public function getBreadcrumb(): string
    {
        return 'Edit';
    }

    public function getSubNavigationLabel(): string
    {
        return (string) ($this->record->nama_akun ?? 'COA');
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
        return $this->getResource()::getUrl('index');
    }
}