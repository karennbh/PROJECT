<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Admin\Widgets\DashboardInventarisChartWidget;
use App\Filament\Admin\Widgets\DashboardPengajuanChartWidget;
use App\Filament\Admin\Widgets\StatistikPenjualanWidget;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatistikPenjualanWidget::class,
            DashboardPengajuanChartWidget::class,
            DashboardInventarisChartWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
