<?php

namespace App\Filament\Admin\Resources\PendapatanHibahs\Tables;

use App\Filament\Admin\Resources\PerolehanBarangs\PerolehanBarangResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PendapatanHibahsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_hibah')
                    ->label('Nomor Hibah')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('tanggal_hibah')
                    ->label('Tanggal Hibah')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('sumber_hibah')
                    ->label('Sumber Hibah')
                    ->searchable(),

                TextColumn::make('nilai_hibah')
                    ->label('Nilai Hibah')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->alignEnd(),

                TextColumn::make('digunakan')
                    ->label('Sudah Digunakan')
                    ->state(fn ($record) => $record->digunakan)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->alignEnd(),

                TextColumn::make('sisa')
                    ->label('Sisa Hibah')
                    ->state(fn ($record) => $record->sisa)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((int) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'success' : 'gray'),
            ])
            ->recordAction('view')
            ->recordUrl(null)
            ->recordActions([
                Action::make('lanjutPerolehanAset')
                    ->label('Lanjut Perolehan Aset')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->visible(fn ($record) => $record->sisa > 0)
                    ->url(fn ($record) => PerolehanBarangResource::getUrl('create', [
                        'sumber_perolehan' => 'hibah_uang',
                        'pendapatan_hibah_id' => $record->no_hibah,
                    ])),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('tanggal_hibah', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada pendapatan hibah')
            ->emptyStateDescription('Tambahkan pendapatan hibah untuk digunakan pada perolehan aset.');
    }
}
