<?php

namespace App\Filament\Admin\Resources\KategoriAsetTetaps\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;

class KategoriAsetTetapsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_kategori_aset')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->color('primary'),

                TextColumn::make('nama_kategori_aset')
                    ->label('Kelompok Aset Tetap')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('umur_ekonomis')
                    ->label('Umur Ekonomis')
                    ->formatStateUsing(fn ($state) => "{$state} tahun")
                    ->sortable(),

                TextColumn::make('tarif_penyusutan')
                    ->label('Tarif Penyusutan')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->sortable(),
                    // ->alignRight(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->wrap(),

                // TextColumn::make('created_at')
                //     ->label('Dibuat')
                //     ->dateTime('d M Y H:i')
                //     ->sortable(),

                // TextColumn::make('updated_at')
                //     ->label('Diupdate')
                //     ->dateTime('d M Y H:i')
                //     ->sortable(),
            ])
            ->filters([])
            ->recordAction('view')
            ->recordUrl(null)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('id_kategori_aset', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada kategori aset')
            ->emptyStateDescription('Tambahkan kategori aset tetap untuk mengatur umur ekonomis dan tarif penyusutan.');
    }
}
