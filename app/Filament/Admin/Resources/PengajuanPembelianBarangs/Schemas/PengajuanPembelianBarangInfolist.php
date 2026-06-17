<?php

namespace App\Filament\Admin\Resources\PengajuanPembelianBarangs\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PengajuanPembelianBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('id_pembelian_barang_kantor')->label('No Pengajuan'),
                TextEntry::make('user.name')->label('Nama Pemohon'),
                TextEntry::make('tanggal_pengajuan')->label('Tanggal Pengajuan')->date('d M Y'),
                TextEntry::make('nama_barang')->label('Nama Barang'),
                TextEntry::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'aset' => 'Aset',
                        'bhp' => 'BHP',
                        default => '-',
                    }),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'warning',
                    }),
                TextEntry::make('perkiraan_harga')
                    ->label('Perkiraan Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                TextEntry::make('jumlah')->label('Jumlah'),
                TextEntry::make('sub_total')
                    ->label('Total Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                TextEntry::make('link_barang')->label('Link Barang')->columnSpanFull()->placeholder('-'),
                TextEntry::make('alasan')->label('Alasan Pengajuan')->columnSpanFull(),
                ImageEntry::make('bukti_pendukung')->label('Bukti Pendukung')->disk('public')->columnSpanFull(),
            ]);
    }
}
