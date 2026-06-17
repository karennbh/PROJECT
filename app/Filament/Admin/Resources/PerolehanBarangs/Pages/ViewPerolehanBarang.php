<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs\Pages;

use App\Filament\Admin\Resources\PerolehanBarangs\PerolehanBarangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPerolehanBarang extends ViewRecord
{
    protected static string $resource = PerolehanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
