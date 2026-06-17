<?php

namespace App\Filament\Admin\Resources\PeminjamanBarangs\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PeminjamanBarangInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('id_peminjaman')
                    ->label('No Peminjaman Barang Kantor'),

                TextEntry::make('user.name')
                    ->label('Nama Peminjam'),

                TextEntry::make('nama_barang')
                    ->label('Nama Barang')
                    ->state(fn ($record) => $record->nama_barang ?? $record->barang?->nama_barang ?? '-'),

                TextEntry::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'aset' => 'Aset Tetap',
                        'bhp' => 'Barang Habis Pakai',
                        default => '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'aset' => 'info',
                        'bhp' => 'success',
                        default => 'gray',
                    }),

                TextEntry::make('kode_barang')
                    ->label('Kode Barang'),

                TextEntry::make('tanggal_pinjam')
                    ->label('Tanggal Pinjam')
                    ->date('d M Y'),

                TextEntry::make('tanggal_pengembalian')
                    ->label('Tanggal Kembali')
                    ->date('d M Y'),

                TextEntry::make('jumlah_pinjam')
                    ->label('Jumlah Pinjam'),

                TextEntry::make('status_pinjam')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'expired' => 'Expired',
                        'menunggu_verifikasi_pengembalian' => 'Menunggu Verifikasi Admin',
                        'disetujui' => 'Disetujui',
                        'kembali' => 'Kembali',
                        'ditolak' => 'Ditolak',
                        default => 'Pending',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'disetujui' => 'success',
                        'kembali' => 'primary',
                        'expired' => 'danger',
                        'ditolak' => 'danger',
                        'menunggu_verifikasi_pengembalian' => 'warning',
                        default => 'warning',
                    }),

                TextEntry::make('alasan_peminjaman')
                    ->label('Alasan Peminjaman Barang Kantor')
                    ->columnSpanFull(),

                ImageEntry::make('bukti_peminjaman')
                    ->label('Bukti Peminjaman Barang Kantor')
                    ->disk('public')
                    ->columnSpan(1),

                ImageEntry::make('bukti_pengembalian')
                    ->label('Bukti Pengembalian')
                    ->disk('public')
                    ->columnSpan(1),
            ]);
    }
}
