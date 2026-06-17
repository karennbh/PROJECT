<?php

namespace App\Filament\Admin\Resources\BarangKantors\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;

class BarangKantorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kode_barang')
                    ->label('Kode Barang'),

                TextEntry::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->date('d/m/Y')
                    ->placeholder('-'),

                TextEntry::make('status_penggunaan')
                    ->label('Status Penggunaan')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'belum_siap_digunakan' => 'Belum Siap Digunakan',
                        'siap_digunakan' => 'Siap Digunakan',
                        default => '-',
                    })
                    ->visible(fn ($record) => $record->kategori_barang === 'aset'),

                TextEntry::make('nama_barang')
                    ->label('Nama Barang'),

                TextEntry::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->formatStateUsing(fn ($state) =>
                        $state === 'aset' ? 'Aset Tetap' : 'Bahan Habis Pakai'
                    ),

                TextEntry::make('jenis_aset_label')
                    ->label('Jenis Aset')
                    ->visible(fn ($record) => $record->kategori_barang === 'aset'),

                TextEntry::make('jenis_barang_label')
                    ->label('Jenis BHP')
                    ->visible(fn ($record) => $record->kategori_barang === 'bhp'),

                TextEntry::make('kategoriAset.nama_kategori_aset')
                    ->label('Kategori Aset Tetap')
                    ->visible(fn ($record) => $record->kategori_barang === 'aset'),

                TextEntry::make('umur_ekonomis')
                    ->label('Umur Ekonomis (Tahun)')
                    ->formatStateUsing(fn ($state) => $state ? $state . ' tahun' : '-')
                    ->visible(fn ($record) => $record->kategori_barang === 'aset'),

                TextEntry::make('nilai_residu')
                    ->label('Nilai Residu')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state) : '-')
                    ->visible(fn ($record) => $record->kategori_barang === 'aset'),

                TextEntry::make('harga_perolehan')
                    ->label('Nilai Perolehan')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->visible(fn ($record) => $record->kategori_barang === 'aset'),

                TextEntry::make('status_barang')
                    ->label('Status Barang')
                    ->visible(fn ($record) => $record?->kategori_barang === 'aset'),
                    
                TextEntry::make('stok')
                    ->label('Stok'),

                TextEntry::make('satuan')
                    ->label('Satuan'),

                TextEntry::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }
}
