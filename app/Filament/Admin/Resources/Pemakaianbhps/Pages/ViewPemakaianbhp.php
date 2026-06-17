<?php

namespace App\Filament\Admin\Resources\Pemakaianbhps\Pages;

use App\Filament\Admin\Resources\Pemakaianbhps\PemakaianbhpResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPemakaianbhp extends ViewRecord
{
    protected static string $resource = PemakaianbhpResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
