<?php

namespace App\Filament\Admin\Resources\BarangKantors\Widgets;

use App\Models\BarangKantor;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class BhpTable extends BaseWidget
{
    protected string $view = 'filament.admin.resources.barang-kantors.widgets.blue-table-widget';

    protected static ?string $heading = 'Barang Habis Pakai';

    protected static ?string $jenisBhp = null;

    protected int|string|array $columnSpan = 'full';

    public function headingLabel(): ?string
    {
        return static::$heading;
    }

    protected function makeTable(): Table
    {
        return parent::makeTable()
            ->queryStringIdentifier($this->getTableQueryStringIdentifier());
    }

    protected function getTableQueryStringIdentifier(): string
    {
        return match (static::$jenisBhp) {
            BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR => 'barangKantorBhpAtk',
            BarangKantor::JENIS_BHP_INVENTARIS_KANTOR => 'barangKantorBhpInventaris',
            default => 'barangKantorBhp',
        };
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        return BarangKantor::query()
            ->where('kategori_barang', 'bhp')
            ->when(static::$jenisBhp, fn (Builder $query, string $jenisBhp) => $query->where('jenis_bhp', $jenisBhp));
    }

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

            TextColumn::make('status_stok')
                ->label('Status')
                ->badge()
                ->alignCenter()
                ->color(fn ($record) => match (true) {
                    $record->status_stok_bhp === BarangKantor::STATUS_STOK_HABIS => 'danger',
                    $record->status_stok_bhp === BarangKantor::STATUS_STOK_MENIPIS => 'warning',
                    default => 'success',
                })
                ->state(fn ($record) => $record->status_stok_bhp)
                ->width('15%'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status_stok')
                ->label('Status Stok')
                ->placeholder('Semua')
                ->options([
                    'tersedia' => 'Tersedia',
                    'menipis'  => 'Menipis',
                    'habis'    => 'Habis',
                ])
                ->query(function (Builder $query, array $data) {
                    return match ($data['value'] ?? null) {
                        'habis'    => $query->bhpStokHabis(),
                        'menipis'  => $query->bhpStokMenipis(),
                        'tersedia' => $query->bhpStokTersedia(),
                        default    => $query,
                    };
                }),

            Filter::make('stok_habis')
                ->label('Hanya Barang Habis')
                ->query(fn (Builder $query) => $query->bhpStokHabis()),
        ];
    }
}
