<?php

namespace App\Filament\Admin\Resources\COAS\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;

class COASTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('kode_akun')
                    ->label('Kode Akun')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('nama_akun')
                    ->label('Nama Akun')
                    ->searchable(),

                TextColumn::make('header_akun')
                    ->label('Header Akun')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'harta' => 'primary',
                        'beban' => 'info',
                        'pendapatan' => 'gray',
                        'kewajiban' => 'warning',
                        'modal' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('saldo')
                    ->label('Posisi Saldo')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => strtoupper((string) $state))
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'debit'  => 'success',
                        'kredit' => 'warning',
                        default  => 'gray',
                    }),

                TextColumn::make('jumlah_saldo')
                    ->label('Saldo (Rp)')
                    ->money('IDR', locale: 'id')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),
            ])

            ->defaultSort('kode_akun', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada COA')
            ->emptyStateDescription('Tambahkan data COA agar daftar akun dapat digunakan pada transaksi.')
            ->filters([])

            ->recordAction('view')
            ->recordUrl(null)
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->url(fn ($record): string => \App\Filament\Admin\Resources\COAS\COAResource::getUrl('edit', ['record' => $record])),
            ])

            ->bulkActions([]);
    }
}
