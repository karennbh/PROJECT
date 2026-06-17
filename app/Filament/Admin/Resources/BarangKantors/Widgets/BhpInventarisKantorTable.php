<?php

namespace App\Filament\Admin\Resources\BarangKantors\Widgets;

use App\Models\BarangKantor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class BhpInventarisKantorTable extends BhpTable
{
    protected string $view = 'filament.admin.resources.barang-kantors.widgets.bhp-nested-table-widget';

    protected static ?string $heading = 'BHP - BPP Inventaris Kantor';

    protected static ?string $jenisBhp = BarangKantor::JENIS_BHP_INVENTARIS_KANTOR;

    /**
     * Override kolom: BPP Inventaris Kantor hanya punya status Tersedia / Habis.
     * Tidak ada status "Menipis" untuk jenis ini.
     */
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('kode_barang')
                ->label('Kode BHP')
                ->searchable()
                ->width('15%'),

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
                ->extraAttributes([
                    'style' => 'width:260px;max-width:260px;white-space:normal;vertical-align:top;',
                ])
                ->formatStateUsing(fn ($state) => "<div style='max-width:260px;white-space:normal;word-break:break-word;line-height:1.7'>" . e($state) . "</div>")
                ->sortable()
                ->searchable()
                ->width('25%'),

            ImageColumn::make('foto')
                ->label('Foto')
                ->disk('public')
                ->height(180)
                ->extraImgAttributes([
                    'style' => 'object-fit: contain; width: 180px; height: 180px; background: #ffffff; border-radius: 18px; border: 1px solid #dbeafe; display: block; margin: 0 auto; padding: 8px;',
                ])
                ->width('32%')
                ->alignCenter(),

            TextColumn::make('stok')
                ->label('Stok')
                ->alignCenter()
                ->width('10%'),

            TextColumn::make('satuan')
                ->label('Satuan')
                ->alignCenter()
                ->width('10%'),

            // BPP Inventaris Kantor: Tersedia / Sedang Dipinjam / Telah Didistribusikan
            TextColumn::make('status_pinjam')
                ->label('Status')
                ->badge()
                ->alignCenter()
                ->color(fn ($record) => match (true) {
                    $record->status_pinjam === BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN => 'gray',
                    $record->status_pinjam === BarangKantor::STATUS_PINJAM_DIPINJAM        => 'info',
                    (int) $record->stok <= 0                                               => 'danger',
                    default                                                                => 'success',
                })
                ->state(fn ($record) => match (true) {
                    $record->status_pinjam === BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN => 'Telah Didistribusikan',
                    $record->status_pinjam === BarangKantor::STATUS_PINJAM_DIPINJAM        => 'Sedang Dipinjam',
                    (int) $record->stok <= 0                                               => BarangKantor::STATUS_STOK_HABIS,
                    default                                                                => BarangKantor::STATUS_PINJAM_TERSEDIA,
                })
                ->width('15%'),
        ];
    }

    /**
     * Override filter: hanya Tersedia / Habis (tidak ada pilihan Menipis).
     */
    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status_stok')
                ->label('Status')
                ->placeholder('Semua')
                ->options([
                    'tersedia'         => 'Tersedia',
                    'dipinjam'         => 'Sedang Dipinjam',
                    'didistribusikan'  => 'Telah Didistribusikan',
                    'habis'            => 'Habis',
                ])
                ->query(function (Builder $query, array $data) {
                    return match ($data['value'] ?? null) {
                        'habis'           => $query->bhpStokHabis(),
                        'tersedia'        => $query->bhpStokTersedia()
                                                ->where('status_pinjam', BarangKantor::STATUS_PINJAM_TERSEDIA),
                        'dipinjam'        => $query->where('status_pinjam', BarangKantor::STATUS_PINJAM_DIPINJAM),
                        'didistribusikan' => $query->where('status_pinjam', BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN),
                        default           => $query,
                    };
                }),
        ];
    }
}
