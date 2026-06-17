<?php

namespace App\Filament\Admin\Resources\Pemakaianbhps\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PemakaianbhpInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('id_pemakaian')
                    ->label('No Pemakaian'),

                TextEntry::make('user.name')
                    ->label('Nama Pengguna'),

                TextEntry::make('nama_barang')
                    ->label('Nama Barang BHP')
                    ->state(fn ($record) => $record->nama_barang ?? $record->barang?->nama_barang ?? '-'),

                TextEntry::make('kode_barang')
                    ->label('Kode Barang'),

                TextEntry::make('tanggal_pemakaian')
                    ->label('Tanggal Penggunaan')
                    ->date('d M Y'),

                TextEntry::make('jumlah')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, $record) => ($state ?? 0) . ' ' . ($record->barang?->satuan ?: '')),

                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'warning',
                    }),

                TextEntry::make('barang.stok')
                    ->label('Stok Saat Ini')
                    ->formatStateUsing(fn ($state, $record) => ($state ?? 0) . ' ' . ($record->barang?->satuan ?: '')),

                TextEntry::make('alasan_kebutuhan')
                    ->label('Alasan Kebutuhan')
                    ->columnSpanFull(),

                ImageEntry::make('bukti_pendukung')
                    ->label('Bukti Pendukung')
                    ->disk('public')
                    ->columnSpanFull(),
            ]);
    }
}
