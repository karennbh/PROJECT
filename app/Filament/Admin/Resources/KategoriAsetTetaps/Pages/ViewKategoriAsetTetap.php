<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps\Pages;

use App\Filament\Admin\Resources\KategoriAsetTetaps\KategoriAsetTetapResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKategoriAsetTetap extends ViewRecord
{
    protected static string $resource = KategoriAsetTetapResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
