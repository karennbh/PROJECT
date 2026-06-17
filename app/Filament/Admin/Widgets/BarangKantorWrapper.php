<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class BarangKantorWrapper extends Widget
{
    // HARUS NON-STATIC
    protected string $view = 'filament.admin.widgets.barang-kantor-wrapper';

    protected int|string|array $columnSpan = 'full';
}
