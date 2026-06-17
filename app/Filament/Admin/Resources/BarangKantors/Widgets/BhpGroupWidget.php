<?php

namespace App\Filament\Admin\Resources\BarangKantors\Widgets;

use Filament\Widgets\Widget;

class BhpGroupWidget extends Widget
{
    protected string $view = 'filament.admin.resources.barang-kantors.widgets.bhp-group-widget';

    protected int|string|array $columnSpan = 'full';

    public function headingLabel(): string
    {
        return 'Barang Habis Pakai';
    }
}
