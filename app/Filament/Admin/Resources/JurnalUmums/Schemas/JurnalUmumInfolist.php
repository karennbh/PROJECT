<?php

namespace App\Filament\Admin\Resources\JurnalUmums\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JurnalUmumInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('reff_transaksi')
                    ->label('Nomor Bukti'),

                TextEntry::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y'),

                TextEntry::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'perolehan_barang', 'pembelian_barang' => 'Perolehan Barang',
                        'penyusutan' => 'Penyusutan',
                        'pengisian_kas_kecil' => 'Pengisian Kas Kecil',
                        'pendapatan_hibah' => 'Pendapatan Hibah',
                        default => ucfirst($state ?? '-'),
                    }),

                TextEntry::make('deskripsi')
                    ->label('Deskripsi')
                    ->placeholder('-')
                    ->columnSpanFull(),

                RepeatableEntry::make('details')
                    ->label('Detail Jurnal')
                    ->schema([
                        TextEntry::make('coa.kode_akun')
                            ->label('Kode Akun')
                            ->placeholder('-'),

                        TextEntry::make('coa.nama_akun')
                            ->label('Nama Akun')
                            ->placeholder('-'),

                        TextEntry::make('nominal_debit')
                            ->label('Debit')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),

                        TextEntry::make('nominal_kredit')
                            ->label('Kredit')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.')),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
