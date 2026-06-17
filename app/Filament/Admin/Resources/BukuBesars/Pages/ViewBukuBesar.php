<?php

namespace App\Filament\Admin\Resources\BukuBesars\Pages;

use App\Filament\Admin\Resources\BukuBesars\BukuBesarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBukuBesar extends ViewRecord
{
    protected static string $resource = BukuBesarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
