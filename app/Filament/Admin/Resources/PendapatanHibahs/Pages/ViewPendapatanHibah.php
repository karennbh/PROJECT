<?php

namespace App\Filament\Admin\Resources\PendapatanHibahs\Pages;

use App\Filament\Admin\Resources\PendapatanHibahs\PendapatanHibahResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPendapatanHibah extends ViewRecord
{
    protected static string $resource = PendapatanHibahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
