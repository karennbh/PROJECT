<?php

namespace App\Filament\Admin\Resources\JurnalUmums\Pages;

use App\Filament\Admin\Resources\JurnalUmums\JurnalUmumResource;
use Filament\Resources\Pages\ListRecords;

class ListJurnalUmums extends ListRecords
{
    protected static string $resource = JurnalUmumResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Admin\Resources\JurnalUmums\Widgets\JurnalUmumOverview::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
