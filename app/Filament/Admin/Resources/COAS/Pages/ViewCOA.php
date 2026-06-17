<?php

namespace App\Filament\Admin\Resources\COAS\Pages;

use App\Filament\Admin\Resources\COAS\COAResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCOA extends ViewRecord
{
    protected static string $resource = COAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
