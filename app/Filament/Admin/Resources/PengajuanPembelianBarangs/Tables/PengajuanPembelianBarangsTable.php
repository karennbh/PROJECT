<?php

namespace App\Filament\Admin\Resources\PengajuanPembelianBarangs\Tables;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PengajuanPembelianBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('lihat')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id_pembelian_barang_kantor')
                    ->label('No')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->badge()
                    ->colors([
                        'primary' => 'aset',
                        'info' => 'bhp',
                    ]),
                TextColumn::make('perkiraan_harga')
                    ->label('Perkiraan Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('jumlah')
                    ->numeric()
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('sub_total')
                    ->label('Total Harga')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->sortable(),
                ImageColumn::make('bukti_pendukung')
                    ->label('Bukti')
                    ->disk('public')
                    ->height(180)
                    ->extraImgAttributes([
                        'style' => 'object-fit: contain; width: 180px; height: 180px; border-radius: 18px; display: block; margin: 0 auto; background: #ffffff; border: 1px solid #dbeafe; padding: 8px;',
                    ])
                    ->width('32%')
                    ->alignCenter()
                    ->defaultImageUrl('https://via.placeholder.com/500x500/e5e7eb/6b7280?text=No+Image'),
                TextColumn::make('alasan')
                    ->label('Keterangan')
                    ->limit(35)
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actionsColumnLabel('Approval')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                SelectFilter::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->placeholder('Semua kategori')
                    ->options([
                        'aset' => 'Aset',
                        'bhp' => 'BHP (Habis Pakai)',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Nama Pemohon')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua pemohon'),
            ])
            ->filtersFormColumns(1)
            ->filtersFormWidth(Width::ExtraSmall)
            ->filtersFormMaxHeight('75vh')
            ->filtersTriggerAction(
                fn (Action $action): Action => $action->label('Filter')
            )
            ->actions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->extraAttributes([
                        'class' => 'hidden',
                    ])
                    ->modalHeading(fn ($record) => 'Lihat ' . ($record->nama_barang ?? 'Pengajuan'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->fillForm(fn ($record) => [
                        'id_pembelian_barang_kantor' => $record->id_pembelian_barang_kantor,
                        'nama_pemohon' => $record->user?->name,
                        'nama_barang' => $record->nama_barang,
                        'kategori_barang' => strtoupper($record->kategori_barang ?? '-'),
                        'jumlah' => $record->jumlah,
                        'perkiraan_harga' => 'Rp ' . number_format((int) $record->perkiraan_harga, 0, ',', '.'),
                        'sub_total' => 'Rp ' . number_format((int) $record->sub_total, 0, ',', '.'),
                        'status' => ucfirst($record->status ?? '-'),
                        'alasan' => $record->alasan,
                    ])
                    ->form([
                        TextInput::make('id_pembelian_barang_kantor')->label('ID Pembelian'),
                        TextInput::make('nama_pemohon')->label('Nama Pemohon'),
                        TextInput::make('nama_barang')->label('Nama Barang'),
                        TextInput::make('kategori_barang')->label('Kategori Barang'),
                        TextInput::make('jumlah')->label('Jumlah'),
                        TextInput::make('perkiraan_harga')->label('Perkiraan Harga'),
                        TextInput::make('sub_total')->label('Total Harga'),
                        TextInput::make('status')->label('Status'),
                        Textarea::make('alasan')->label('Alasan')->rows(4),
                    ])
                    ->disabledForm(),
                Action::make('proses_pesanan')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Pengajuan Pembelian')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui dan memproses pesanan ini? Notifikasi akan dikirim ke pemohon.')
                    ->modalSubmitActionLabel('Ya, Proses')
                    ->action(function ($record) {
                        $record->update(['status' => 'disetujui']);

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Pengajuan disetujui. Notifikasi dikirim ke pemohon.')
                            ->success()
                            ->send();

                        if ($record->user) {
                            Notification::make()
                                ->title('Pengajuan Disetujui')
                                ->body("Halo {$record->user->name}, pengajuan pembelian '{$record->nama_barang}' Anda telah disetujui dan sedang dalam proses pemesanan.")
                                ->success()
                                ->sendToDatabase($record->user);
                        }
                    }),
                Action::make('tolak_pesanan')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan')
                    ->action(function ($record) {
                        $record->update(['status' => 'ditolak']);

                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->warning()
                            ->send();

                        if ($record->user) {
                            Notification::make()
                                ->title('Pengajuan Ditolak')
                                ->body("Maaf, pengajuan pembelian '{$record->nama_barang}' Anda ditolak.")
                                ->danger()
                                ->sendToDatabase($record->user);
                        }
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada pengajuan pembelian')
            ->emptyStateDescription('Pengajuan pembelian akan tampil setelah dibuat oleh pengguna.');
    }
}
