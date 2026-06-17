<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BarangKantor;
use Filament\Widgets\ChartWidget;

class DashboardInventarisChartWidget extends ChartWidget
{
    protected ?string $heading = 'Komposisi Inventaris Saat Ini';

    protected ?string $description = 'Ringkasan status aset tetap dan stok BHP berdasarkan data barang kantor.';

    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $asetAktif = BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->where('status_barang', 'Aktif')
            ->where('status_pinjam', BarangKantor::STATUS_PINJAM_TERSEDIA)
            ->count();

        $asetDipinjam = BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->where('status_pinjam', BarangKantor::STATUS_PINJAM_DIPINJAM)
            ->count();

        $asetNonaktif = BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->where('status_barang', '!=', 'Aktif')
            ->count();

        $bhpTersedia = BarangKantor::query()
            ->bhpStokTersedia()
            ->count();

        $bhpMenipis = BarangKantor::query()
            ->bhpStokMenipis()
            ->count();

        $bhpHabis = BarangKantor::query()
            ->bhpStokHabis()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Inventaris',
                    'data' => [
                        $asetAktif,
                        $asetDipinjam,
                        $asetNonaktif,
                        $bhpTersedia,
                        $bhpMenipis,
                        $bhpHabis,
                    ],
                    'backgroundColor' => [
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#0ea5e9',
                        '#f97316',
                        '#94a3b8',
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                'Aset Aktif',
                'Aset Dipinjam',
                'Aset Nonaktif',
                'BHP Tersedia',
                'BHP Menipis',
                'BHP Habis',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
