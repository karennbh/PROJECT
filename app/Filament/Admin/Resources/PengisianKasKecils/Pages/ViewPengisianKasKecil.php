<?php

namespace App\Filament\Admin\Resources\PengisianKasKecils\Pages;

use App\Filament\Admin\Resources\PengisianKasKecils\PengisianKasKecilResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPengisianKasKecil extends ViewRecord
{
    protected static string $resource = PengisianKasKecilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
