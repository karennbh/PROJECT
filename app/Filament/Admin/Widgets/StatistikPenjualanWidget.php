<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BarangKantor;
use App\Models\PemakaianBHP;
use App\Models\PengajuanPembelianBarang;
use App\Models\PeminjamanBarang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikPenjualanWidget extends BaseWidget
{
    protected ?string $pollingInterval = '15s';

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    public function getStats(): array
    {
        $totalAset = (int) BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->count();

        $jumlahUnitAset = (int) BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->sum('stok') ?: $totalAset;

        $asetAktif = (int) BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->where('status_barang', 'Aktif')
            ->count();

        $asetDipinjam = (int) BarangKantor::query()
            ->where('kategori_barang', 'aset')
            ->where('status_pinjam', BarangKantor::STATUS_PINJAM_DIPINJAM)
            ->count();

        $totalBhp = (int) BarangKantor::query()
            ->where('kategori_barang', 'bhp')
            ->count();

        $jumlahStokBhp = (int) BarangKantor::query()
            ->where('kategori_barang', 'bhp')
            ->sum('stok');

        $bhpMenipis = (int) BarangKantor::query()
            ->bhpStokMenipis()
            ->count();

        $bhpHabis = (int) BarangKantor::query()
            ->bhpStokHabis()
            ->count();

        $totalPeminjaman = (int) PeminjamanBarang::query()->count();
        $totalPemakaian = (int) PemakaianBHP::query()->count();
        $totalPerolehan = (int) PengajuanPembelianBarang::query()->count();

        $pinjamPending = (int) PeminjamanBarang::query()
            ->where('status_pinjam', 'pending')
            ->count();

        $pinjamDisetujui = (int) PeminjamanBarang::query()
            ->where('status_pinjam', 'disetujui')
            ->count();

        $pemakaianPending = (int) PemakaianBHP::query()
            ->where('status', 'pending')
            ->count();

        $perolehanPending = (int) PengajuanPembelianBarang::query()
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Total Aset Tetap', number_format($totalAset, 0, ',', '.'))
                ->description('Unit tersedia: ' . number_format($jumlahUnitAset, 0, ',', '.') . ' | Aktif: ' . number_format($asetAktif, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->icon('heroicon-o-computer-desktop')
                ->chart([2, 4, 5, 6, 7, 8, 9]),

            Stat::make('Total Barang Habis Pakai', number_format($totalBhp, 0, ',', '.'))
                ->description('Stok gudang: ' . number_format($jumlahStokBhp, 0, ',', '.') . ' | Menipis/Habis: ' . number_format($bhpMenipis + $bhpHabis, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary')
                ->icon('heroicon-o-swatch')
                ->chart([9, 8, 8, 7, 6, 5, 4]),

            Stat::make('Total Pengajuan Perolehan', number_format($totalPerolehan, 0, ',', '.'))
                ->description('Menunggu persetujuan: ' . number_format($perolehanPending, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info')
                ->icon('heroicon-o-shopping-bag')
                ->chart([1, 2, 3, 2, 4, 5, 6]),

            Stat::make('Total Peminjaman Barang Kantor', number_format($totalPeminjaman, 0, ',', '.'))
                ->description('Menunggu: ' . number_format($pinjamPending, 0, ',', '.') . ' | Sedang dipinjam: ' . number_format($pinjamDisetujui + $asetDipinjam, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list')
                ->chart([3, 4, 5, 7, 6, 8, 7]),

            Stat::make('Total Pemakaian BHP', number_format($totalPemakaian, 0, ',', '.'))
                ->description('Menunggu proses: ' . number_format($pemakaianPending, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success')
                ->icon('heroicon-o-document-minus')
                ->chart([2, 3, 4, 5, 5, 6, 7]),
        ];
    }
}
