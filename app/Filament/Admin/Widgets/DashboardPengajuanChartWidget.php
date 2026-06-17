<?php

namespace App\Filament\Admin\Widgets;

use App\Models\PengajuanPembelianBarang;
use App\Models\PemakaianBHP;
use App\Models\PeminjamanBarang;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class DashboardPengajuanChartWidget extends ChartWidget
{
    protected ?string $heading = 'Tren Pengajuan 6 Bulan Terakhir';

    protected ?string $description = 'Perbandingan jumlah pengajuan perolehan, peminjaman, dan pemakaian dari fitur yang sudah berjalan.';

    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset))
            ->push(now()->startOfMonth());

        $labels = $months
            ->map(fn (Carbon $date) => $date->translatedFormat('M Y'))
            ->all();

        $perolehan = $months
            ->map(fn (Carbon $date) => PengajuanPembelianBarang::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count())
            ->all();

        $peminjaman = $months
            ->map(fn (Carbon $date) => PeminjamanBarang::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count())
            ->all();

        $pemakaian = $months
            ->map(fn (Carbon $date) => PemakaianBHP::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count())
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Perolehan',
                    'data' => $perolehan,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.14)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
                [
                    'label' => 'Peminjaman',
                    'data' => $peminjaman,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.12)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
                [
                    'label' => 'Pemakaian',
                    'data' => $pemakaian,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'tension' => 0.35,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

