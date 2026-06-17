<?php

namespace App\Filament\Admin\Resources\BukuBesars\Pages;

use App\Filament\Admin\Resources\BukuBesars\BukuBesarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBukuBesar extends CreateRecord
{
    protected static string $resource = BukuBesarResource::class;
    public static function canCreate(): bool
    {
        return false;
    }
}
