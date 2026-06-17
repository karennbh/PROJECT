<?php

namespace App\Filament\Admin\Resources\BarangKantors\Widgets;

use App\Models\BarangKantor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AsetTetapTable extends BaseWidget
{
    protected string $view = 'filament.admin.resources.barang-kantors.widgets.blue-table-widget';

    protected static ?string $heading = 'Aset Tetap ';

    protected int|string|array $columnSpan = 'full';

    public function headingLabel(): ?string
    {
        return static::$heading;
    }

    protected function makeTable(): Table
    {
        return parent::makeTable()
            ->queryStringIdentifier('barangKantorAsetTetap');
    }

    protected function getTableQuery(): Builder
    {
        return BarangKantor::query()
            ->where('kategori_barang', 'aset');
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('kategori_aset_id')
                ->label('Kategori Aset Tetap')
                ->placeholder('Semua')
                ->relationship('kategoriAset', 'nama_kategori_aset')
                ->preload(),

            SelectFilter::make('status_barang')
                ->label('Status')
                ->placeholder('Semua')
                ->options([
                    'Aktif' => 'Aktif',
                    'Tidak Aktif' => 'Tidak Aktif',
                ]),

            SelectFilter::make('status_pinjam')
                ->label('Ketersediaan')
                ->placeholder('Semua')
                ->options([
                    BarangKantor::STATUS_PINJAM_TERSEDIA => 'Tersedia',
                    BarangKantor::STATUS_PINJAM_DIPINJAM => 'Sedang Dipinjam',
                ]),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('kode_barang')
                ->label('Kode Aset')
                ->width('12%')
                ->searchable(),

            TextColumn::make('barcode')
                ->label('Barcode QR')
                ->html()
                ->width('14%')
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
                ->extraAttributes([
                    'style' => 'width:260px;max-width:260px;white-space:normal;vertical-align:top;',
                ])
                ->formatStateUsing(fn ($state) => "<div style='max-width:260px;white-space:normal;word-break:break-word;line-height:1.7'>" . e($state) . "</div>")
                ->sortable()
                ->width('14%')
                ->searchable(),

            TextColumn::make('jenis_aset_label')
                ->label('Jenis Aset')
                ->badge()
                ->color('info')
                ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('jenis_aset', $direction))
                ->width('8%'),

            TextColumn::make('umur_ekonomis')
                ->label(new HtmlString('Umur Ekonomis<br>(Tahun)'))
                ->searchable()
                ->width('3%')
                ->formatStateUsing(fn ($state) => $state . ' tahun'),

            TextColumn::make('tanggal_diterima')
                ->label('Tanggal Diterima')
                ->date('d/m/Y')
                ->placeholder('-')
                ->width('8%'),

            TextColumn::make('status_penggunaan')
                ->label('Status Penggunaan')
                ->badge()
                ->formatStateUsing(fn ($state) => match ($state) {
                    'belum_siap_digunakan' => 'Belum Siap Digunakan',
                    'siap_digunakan' => 'Siap Digunakan',
                    default => '-',
                })
                ->color(fn ($state) => match ($state) {
                    'belum_siap_digunakan' => 'warning',
                    'siap_digunakan' => 'info',
                    default => 'gray',
                })
                ->width('8%'),

            TextColumn::make('harga_perolehan')
                ->label('Nilai Perolehan')
                ->searchable()
                ->width('10%')
                ->formatStateUsing(fn ($state) => $state
                    ? 'Rp ' . number_format($state, 0, ',', '.')
                    : '-')
                ->alignEnd(),

            ImageColumn::make('foto')
                ->label('Foto')
                ->disk('public')
                ->height(180)
                ->extraImgAttributes([
                    'style' => 'object-fit: contain; width: 180px; height: 180px; border-radius: 18px; display: block; margin: 0 auto; background: #ffffff; border: 1px solid #dbeafe; padding: 8px;',
                ])
                ->width('32%')
                ->alignCenter(),
                
            TextColumn::make('status_barang')
                ->label('Status Aset')
                ->searchable()
                ->badge()
                ->width('7%')
                ->alignCenter()
                ->color(fn (string $state): string|array => match ($state) {
                    'Aktif' => 'success',
                    'Tidak Aktif' => 'danger',
                    default => 'gray',
                }),

            TextColumn::make('status_pinjam')
                ->label('Ketersediaan')
                ->searchable()
                ->badge()
                ->formatStateUsing(fn ($state) => $state ?: '-')
                ->width('6%')
                ->alignCenter()
                ->color(fn (string $state): string|array => match ($state) {
                    BarangKantor::STATUS_PINJAM_TERSEDIA => 'success',
                    BarangKantor::STATUS_PINJAM_DIPINJAM => 'warning',
                    default => 'gray',
                }),

        ];
    }
}
