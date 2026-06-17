<?php

namespace App\Filament\Admin\Resources\JurnalUmums\Pages;

use App\Filament\Admin\Resources\JurnalUmums\JurnalUmumResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJurnalUmum extends CreateRecord
{
    protected static string $resource = JurnalUmumResource::class;
    
    public static function canCreate(): bool
    {
        return false;
    }

}
