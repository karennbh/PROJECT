<?php

namespace App\Filament\Admin\Resources\JurnalUmums\Pages;

use App\Filament\Admin\Resources\JurnalUmums\JurnalUmumResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewJurnalUmum extends ViewRecord
{
    protected static string $resource = JurnalUmumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
