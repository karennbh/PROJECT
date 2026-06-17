<?php

namespace App\Filament\Admin\Resources\BarangKantors\Widgets;

use App\Models\BarangKantor;

class BhpAtkOperasionalTable extends BhpTable
{
    protected string $view = 'filament.admin.resources.barang-kantors.widgets.bhp-nested-table-widget';

    protected static ?string $heading = 'BHP - ATK Operasional Kantor';

    protected static ?string $jenisBhp = BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR;
}
