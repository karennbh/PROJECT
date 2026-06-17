<?php

namespace App\Filament\Admin\Resources\PengisianKasKecils\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengisianKasKecilsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_transaksi')
                    ->label('No Transaksi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(45)
                    ->searchable(),
            ])
            ->recordAction('view')
            ->recordUrl(null)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada pengisian kas kecil')
            ->emptyStateDescription('Tambahkan pengisian kas kecil agar saldo kas kecil bertambah.');
    }
}
