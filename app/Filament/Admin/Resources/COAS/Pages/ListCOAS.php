<?php

namespace App\Filament\Admin\Resources\COAS\Pages;

use App\Filament\Admin\Resources\COAS\COAResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCOAS extends ListRecords
{
    protected static string $resource = COAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah COA'),
        ];
    }
}
