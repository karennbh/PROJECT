<?php

namespace App\Filament\Admin\Resources\BukuBesars\Pages;

use App\Filament\Admin\Resources\BukuBesars\BukuBesarResource;
use Filament\Resources\Pages\Page;

class ListBukuBesars extends Page
{
    protected static string $resource = BukuBesarResource::class;

    protected string $view = 'filament.admin.resources.buku-besars.pages.list-buku-besars';

    public function getTitle(): string
    {
        return 'Buku Besar';
    }

    public function getBreadcrumb(): string
    {
        return 'Daftar';
    }
}
