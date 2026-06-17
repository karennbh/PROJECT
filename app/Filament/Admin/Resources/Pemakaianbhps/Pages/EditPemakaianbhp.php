<?php

namespace App\Filament\Admin\Resources\Pemakaianbhps\Pages;

use App\Filament\Admin\Resources\Pemakaianbhps\PemakaianbhpResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPemakaianbhp extends EditRecord
{
    protected static string $resource = PemakaianbhpResource::class;

    public function getTitle(): string
    {
        return 'Edit Pemakaian BHP';
    }

    public function getBreadcrumb(): string
    {
        return 'Edit';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
