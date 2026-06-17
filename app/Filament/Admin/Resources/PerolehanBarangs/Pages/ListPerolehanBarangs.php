<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs\Pages;

use App\Filament\Admin\Resources\PerolehanBarangs\PerolehanBarangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerolehanBarangs extends ListRecords
{
    protected static string $resource = PerolehanBarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Perolehan Barang'),
        ];
    }
}

