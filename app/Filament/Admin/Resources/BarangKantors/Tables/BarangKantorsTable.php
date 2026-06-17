<?php

namespace App\Filament\Admin\Resources\BarangKantors\Tables;

use App\Models\BarangKantor;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class BarangKantorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('barcode')
                    ->label('Barcode QR')
                    ->html()
                    ->formatStateUsing(fn ($state, $record) => $state
                        ? "<a href='" . e($record->barcode_target_url) . "' target='_blank' rel='noopener noreferrer' style='display:flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none;color:inherit'>
                                <img src='" . e($record->barcode_qr_image_url) . "' alt='QR " . e($state) . "' style='width:90px;height:90px;border-radius:12px;border:1px solid #dbeafe;padding:6px;background:#fff'>
                                <div style='font-size:10px;text-align:center;line-height:1.35'>" . e($record->kode_barang) . "</div>
                           </a>"
                        : "-"
                    ),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->html()
                    ->wrap()
                    ->width('280px')
                    ->extraAttributes([
                        'style' => 'width:280px;max-width:280px;white-space:normal;vertical-align:top;',
                    ])
                    ->formatStateUsing(fn ($state) => "<div style='max-width:280px;white-space:normal;word-break:break-word;line-height:1.7'>" . e($state) . "</div>")
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kategori_barang')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) =>
                        $state === 'aset' ? 'Aset Tetap' : 'Barang Habis Pakai'
                    )
                    ->color(fn ($state) => match ($state) {
                        'aset' => 'success',
                        'bhp'  => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('jenis_aset_label')
                    ->label('Jenis Aset')
                    ->badge()
                    ->color('info')
                    ->visible(fn ($livewire) => true)
                    ->formatStateUsing(fn ($state, $record) => $record?->kategori_barang === 'aset' ? $state : '-')
                    ->placeholder('-'),

                TextColumn::make('jenis_barang_label')
                    ->label('Jenis BHP')
                    ->badge()
                    ->color(fn ($record) => match ($record?->jenis_bhp) {
                        BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR => 'info',
                        BarangKantor::JENIS_BHP_INVENTARIS_KANTOR => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state, $record) => $record?->kategori_barang === 'bhp' ? $state : '-')
                    ->placeholder('-'),

                TextColumn::make('stok')
                    ->alignCenter(),

                TextColumn::make('status_barang')
                    ->label('Status Barang')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Aktif' => 'success',
                        'Tidak Aktif' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status_penggunaan')
                    ->label('Status Penggunaan')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record?->kategori_barang === 'aset'
                        ? match ($state) {
                            'belum_siap_digunakan' => 'Belum Siap Digunakan',
                            'siap_digunakan' => 'Siap Digunakan',
                            default => '-',
                        }
                        : '-')
                    ->color(fn ($state) => match ($state) {
                        'belum_siap_digunakan' => 'warning',
                        'siap_digunakan' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        BarangKantor::STATUS_PINJAM_TERSEDIA          => 'Tersedia',
                        BarangKantor::STATUS_PINJAM_DIPINJAM          => 'Sedang Dipinjam',
                        BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN   => 'Telah Didistribusikan',
                        BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN => 'Tidak untuk Dipinjamkan',
                        default => $state ?: '-',
                    })
                    ->color(fn ($state) => match ($state) {
                        BarangKantor::STATUS_PINJAM_TERSEDIA          => 'success',
                        BarangKantor::STATUS_PINJAM_DIPINJAM          => 'info',      // biru
                        BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN,
                        BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN => 'gray',
                        default                                        => 'gray',
                    }),

                TextColumn::make('harga_perolehan')
                    ->label('Harga Perolehan')
                    ->money('idr')
                    ->visible(fn ($record) => $record?->kategori_barang === 'aset'),
            ])
            ->filters([
                SelectFilter::make('status_barang')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Tidak Aktif' => 'Tidak Aktif',
                    ]),
                SelectFilter::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->options([
                        BarangKantor::STATUS_PINJAM_TERSEDIA          => 'Tersedia',
                        BarangKantor::STATUS_PINJAM_DIPINJAM          => 'Sedang Dipinjam',
                        BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN   => 'Telah Didistribusikan',
                        BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN => 'Tidak untuk Dipinjamkan',
                    ]),
                SelectFilter::make('kategori_barang')
                    ->label('Jenis Barang')
                    ->options([
                        'aset' => 'Aset Tetap',
                        'bhp' => 'Barang Habis Pakai',
                    ]),
                SelectFilter::make('jenis_aset')
                    ->label('Jenis Aset')
                    ->options([
                        'sarana_pendidikan_laboratorium' => 'Sarana Pendidikan Laboratorium',
                        'inventaris_kantor' => 'Inventaris Kantor',
                        'kendaraan' => 'Kendaraan',
                    ]),
                SelectFilter::make('jenis_bhp')
                    ->label('Jenis BHP')
                    ->options([
                        BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR => 'ATK Operasional Kantor',
                        BarangKantor::JENIS_BHP_INVENTARIS_KANTOR => 'BPP Inventaris Kantor',
                    ]),
            ])
            ->recordAction('lihat')
            ->recordUrl(null)
            ->recordActions([
                Action::make('detail_barang')
                    ->label('Detail')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->url(fn ($record) => \App\Filament\Admin\Resources\BarangKantors\BarangKantorResource::getUrl('view', [
                        'record' => $record,
                    ])),
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (BarangKantor $record) => 'Lihat ' . $record->nama_barang)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->fillForm(fn (BarangKantor $record): array => [
                        // ── Umum ──────────────────────────────────────────
                        'kode_barang'        => $record->kode_barang ?: '-',
                        'nama_barang'        => $record->nama_barang ?: '-',
                        'kategori_barang'    => $record->kategori_barang === 'aset' ? 'Aset Tetap' : 'Barang Habis Pakai',
                        'stok'               => (string) ($record->stok ?? 0),
                        'satuan'             => $record->satuan ?: '-',
                        'status_barang'      => $record->status_barang ?: '-',
                        'keterangan'         => $record->keterangan ?: '-',

                        // ── Khusus Aset ───────────────────────────────────
                        'jenis_aset'         => $record->kategori_barang === 'aset'
                            ? ($record->jenis_aset_label ?: '-') : null,
                        'kategori_aset'      => $record->kategori_barang === 'aset'
                            ? ($record->kategoriAset?->nama_kategori_aset ?: '-') : null,
                        'umur_ekonomis'      => $record->kategori_barang === 'aset'
                            ? (($record->umur_ekonomis ?? '-') . ($record->umur_ekonomis ? ' Tahun' : '')) : null,
                        'tarif_penyusutan'   => $record->kategori_barang === 'aset'
                            ? ($record->kategoriAset?->tarif_penyusutan
                                ? $record->kategoriAset->tarif_penyusutan . '%' : '-') : null,
                        'tanggal_diterima'   => $record->kategori_barang === 'aset'
                            ? ($record->tanggal_diterima ? $record->tanggal_diterima->format('d/m/Y') : '-') : null,
                        'status_penggunaan'  => $record->kategori_barang === 'aset'
                            ? match ($record->status_penggunaan) {
                                BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN => 'Belum Siap Digunakan',
                                BarangKantor::STATUS_SIAP_DIGUNAKAN       => 'Siap Digunakan',
                                default                                   => '-',
                            } : null,
                        'harga_perolehan'    => $record->kategori_barang === 'aset'
                            ? 'Rp ' . number_format((int) $record->harga_perolehan, 0, ',', '.') : null,
                        'nilai_residu'       => $record->kategori_barang === 'aset'
                            ? 'Rp ' . number_format((int) $record->nilai_residu, 0, ',', '.') : null,
                        'status_pinjam'      => $record->kategori_barang === 'aset'
                            ? ($record->status_pinjam ?: '-') : null,

                        // ── Khusus BHP ────────────────────────────────────
                        'jenis_bhp'          => $record->kategori_barang === 'bhp'
                            ? ($record->jenis_barang_label ?: '-') : null,
                        'status_stok'        => $record->kategori_barang === 'bhp'
                            ? $record->status_stok_bhp : null,
                    ])
                    ->form(function (BarangKantor $record): array {
                        $isAset = $record->kategori_barang === 'aset';

                        $umum = [
                            Grid::make(2)->schema([
                                TextInput::make('kode_barang')->label('Kode Barang'),
                                TextInput::make('kategori_barang')->label('Kategori Barang'),
                                TextInput::make('nama_barang')->label('Nama Barang')->columnSpan(2),
                            ]),
                        ];

                        $asetFields = $isAset ? [
                            Grid::make(2)->schema([
                                TextInput::make('jenis_aset')->label('Jenis Aset'),
                                TextInput::make('kategori_aset')->label('Kategori Aset Tetap'),
                                TextInput::make('tanggal_diterima')->label('Tanggal Diterima'),
                                TextInput::make('status_penggunaan')->label('Status Penggunaan'),
                                TextInput::make('umur_ekonomis')->label('Umur Ekonomis'),
                                TextInput::make('tarif_penyusutan')->label('Tarif Penyusutan'),
                                TextInput::make('harga_perolehan')->label('Nilai Perolehan'),
                                TextInput::make('nilai_residu')->label('Nilai Residu'),
                                TextInput::make('stok')->label('Stok'),
                                TextInput::make('satuan')->label('Satuan'),
                                TextInput::make('status_barang')->label('Status Barang'),
                                TextInput::make('status_pinjam')->label('Ketersediaan'),
                            ]),
                        ] : [];

                        $bhpFields = ! $isAset ? [
                            Grid::make(2)->schema([
                                TextInput::make('jenis_bhp')->label('Jenis BHP'),
                                TextInput::make('stok')->label('Stok'),
                                TextInput::make('satuan')->label('Satuan'),
                                TextInput::make('status_stok')->label('Status Stok'),
                            ]),
                        ] : [];

                        $footer = [
                            Textarea::make('keterangan')->label('Keterangan')->rows(3)->columnSpanFull(),
                        ];

                        return array_merge($umum, $asetFields, $bhpFields, $footer);
                    })
                    ->disabledForm(),
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada barang kantor')
            ->emptyStateDescription('Tambahkan barang kantor agar data aset dan BHP tampil di tabel.');
    }
}
