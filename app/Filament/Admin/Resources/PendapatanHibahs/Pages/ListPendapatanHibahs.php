<?php

namespace App\Filament\Admin\Resources\PendapatanHibahs\Pages;

use App\Filament\Admin\Resources\PendapatanHibahs\PendapatanHibahResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPendapatanHibahs extends ListRecords
{
    protected static string $resource = PendapatanHibahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Pendapatan Hibah'),
        ];
    }
}
