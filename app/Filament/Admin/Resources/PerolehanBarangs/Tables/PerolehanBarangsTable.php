<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs\Tables;

use App\Models\PerolehanBarang;
use App\Models\PerolehanBarangDetail;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class PerolehanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['details.barangKantor', 'details.kategoriAset']))
            ->columns([
                TextColumn::make('id_perolehan_barang')
                    ->label('Nomor Perolehan')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('sumber_perolehan')
                    ->label('Sumber')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        PerolehanBarang::SUMBER_HIBAH_LEGACY,
                        PerolehanBarang::SUMBER_HIBAH => 'Hibah Barang',
                        PerolehanBarang::SUMBER_HIBAH_UANG => 'Hibah Uang',
                        default => 'Pembelian',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        PerolehanBarang::SUMBER_HIBAH_LEGACY,
                        PerolehanBarang::SUMBER_HIBAH => 'warning',
                        PerolehanBarang::SUMBER_HIBAH_UANG => 'info',
                        default => 'success',
                    }),

                TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal Pembelian')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable(),

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
                    }),

                ImageColumn::make('dokumen_perolehan')
                    ->label('Dokumen')
                    ->getStateUsing(function ($record) {
                        $path = in_array($record->sumber_perolehan, [
                            PerolehanBarang::SUMBER_HIBAH_LEGACY,
                            PerolehanBarang::SUMBER_HIBAH,
                            PerolehanBarang::SUMBER_HIBAH_UANG,
                        ], true)
                            ? $record->bukti_dokumen_hibah
                            : $record->foto_nota;

                        if (! $path) {
                            return null;
                        }

                        if (is_array($path)) {
                            $path = $path[0] ?? null;
                        }

                        if (is_string($path)) {
                            $decoded = json_decode($path, true);

                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $path = $decoded[0] ?? null;
                            }
                        }

                        if (! $path) {
                            return null;
                        }

                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                            return $path;
                        }

                        $path = str_replace('\\', '/', $path);
                        $path = ltrim($path, '/');
                        $path = preg_replace('#^storage/#', '', $path);

                        return asset('storage/' . $path);
                    })
                    ->square()
                    ->size(60)
                    ->alignCenter()
                    ->defaultImageUrl('https://via.placeholder.com/500x500/e5e7eb/6b7280?text=No+Image')
                    ->openUrlInNewTab(),

                TextColumn::make('subtotal_barang')
                    ->label('Subtotal Harga Barang')
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->alignEnd(),

                TextColumn::make('diskon_total')
                    ->label('Total Diskon')
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->alignEnd(),

                TextColumn::make('biaya_lainnya_total')
                    ->label('Total Biaya Lainnya')
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->alignEnd(),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->getStateUsing(fn (PerolehanBarang $record) => self::grandTotalForTable($record))
                    ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ->alignEnd()
                    ->summarize(
                        Summarizer::make()
                            ->label('TOTAL KESELURUHAN')
                            ->using(fn ($query) => $query->get()->sum(fn ($record) => self::grandTotalForTable($record)))
                            ->formatStateUsing(fn ($state) => self::rupiah($state))
                    ),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40)
                    ->wrap()
                    ->placeholder('Tidak ada keterangan'),
            ])
            ->filters([
                Filter::make('tanggal_pembelian')
                    ->label('Filter Tanggal Pembelian')
                    ->form([
                        DatePicker::make('tanggal_awal')
                            ->label('Tanggal Awal')
                            ->live(),
                        DatePicker::make('tanggal_akhir')
                            ->label('Tanggal Akhir')
                            ->minDate(fn (Get $get) => $get('tanggal_awal'))
                            ->rule(fn (Get $get) => 'after_or_equal:' . $get('tanggal_awal')),
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->placeholder('Semua bulan'),
                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(
                                collect(range(2025, now()->year + 6))
                                    ->mapWithKeys(fn ($year) => [(string) $year => (string) $year])
                                    ->all()
                            )
                            ->placeholder('Semua tahun'),
                        Select::make('sumber_perolehan')
                            ->label('Sumber')
                            ->options([
                                PerolehanBarang::SUMBER_PEMBELIAN => 'Pembelian',
                                PerolehanBarang::SUMBER_HIBAH => 'Hibah Barang',
                                PerolehanBarang::SUMBER_HIBAH_UANG => 'Hibah Uang',
                            ])
                            ->placeholder('Semua sumber'),
                    ])
                    ->query(function ($query, array $data) {
                        $tanggalAwal = $data['tanggal_awal'] ?? null;
                        $tanggalAkhir = $data['tanggal_akhir'] ?? null;

                        if ($tanggalAwal && $tanggalAkhir && $tanggalAkhir < $tanggalAwal) {
                            $tanggalAkhir = $tanggalAwal;
                        }

                        return $query
                            ->when($tanggalAwal, fn ($query) => $query->whereDate('tanggal_pembelian', '>=', $tanggalAwal))
                            ->when($tanggalAkhir, fn ($query) => $query->whereDate('tanggal_pembelian', '<=', $tanggalAkhir))
                            ->when($data['bulan'] ?? null, fn ($query, $bulan) => $query->whereMonth('tanggal_pembelian', (int) $bulan))
                            ->when($data['tahun'] ?? null, fn ($query, $tahun) => $query->whereYear('tanggal_pembelian', (int) $tahun))
                            ->when($data['sumber_perolehan'] ?? null, fn ($query, $sumber) => $query->where('sumber_perolehan', $sumber));
                    }),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->label('Filter')
                    ->badge(null)
            )
            ->recordAction('detail_perolehan')
            ->recordUrl(null)
            ->recordActions([
                Action::make('detail_perolehan')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (PerolehanBarang $record) => 'Detail Perolehan ' . $record->id_perolehan_barang)
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (PerolehanBarang $record) => self::detailPerolehanContent($record)),
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada perolehan barang')
            ->emptyStateDescription('Tambahkan perolehan barang baru dengan klik tombol "Tambah Perolehan Barang".');
    }

    private static function detailPerolehanContent(PerolehanBarang $record): HtmlString
    {
        $record->loadMissing(['details.barangKantor', 'details.kategoriAset', 'pendapatanHibah']);

        $rows = $record->details
            ->values()
            ->map(function (PerolehanBarangDetail $detail, int $index) use ($record): string {
                $namaBarang = $detail->nama_barang ?: $detail->barangKantor?->nama_barang ?: '-';

                return '<tr>'
                    . '<td class="ta-name">' . e($namaBarang) . '</td>'
                    . '<td class="ta-number">' . number_format((int) $detail->jumlah_perolehan, 0, ',', '.') . '</td>'
                    . '<td class="ta-number">' . self::rupiah($detail->harga_satuan ?: $detail->harga_perolehan) . '</td>'
                    . '<td class="ta-number">' . self::rupiah($detail->total_harga) . '</td>'
                    . '<td class="ta-number">' . self::rupiah($detail->alokasi_diskon) . '</td>'
                    . '<td class="ta-number">' . self::rupiah($detail->alokasi_biaya_lainnya) . '</td>'
                    . '<td class="ta-number">' . self::rupiah($detail->harga_perolehan) . '</td>'
                    . '<td class="ta-number">' . self::rupiah($detail->total_harga_perolehan) . '</td>'
                    . '</tr>';
            })
            ->join('');

        if ($rows === '') {
            $rows = '<tr><td colspan="8" class="ta-empty">Belum ada detail barang.</td></tr>';
        }

        $tanggal = $record->tanggal_pembelian
            ? $record->tanggal_pembelian->format('d/m/Y')
            : '-';
        $tanggalSiap = $record->tanggal_diterima
            ? $record->tanggal_diterima->format('d/m/Y')
            : '-';
        $tanggalPerolehanLabel = 'Tanggal Pembelian';
        $total = $record->isHibah()
            ? ((int) $record->total_nilai_hibah ?: (int) $record->details->sum('total_harga_perolehan'))
            : ((int) $record->grand_total ?: (int) $record->details->sum('total_harga_perolehan'));
        $sumberLabel = self::sumberPerolehanLabel($record->sumber_perolehan);
        $totalFormatted = self::rupiah($total);
        $namaPemberiHibah = $record->isHibah()
            ? e($record->nama_pemberi_hibah ?: $record->pendapatanHibah?->sumber_hibah ?: '-')
            : '-';
        $kategoriBarang = $record->details
            ->map(fn (PerolehanBarangDetail $detail) => self::kategoriBarangLabel($detail->kategori_barang))
            ->unique()
            ->values()
            ->join(', ') ?: '-';
        $jenisBhp = $record->details
            ->where('kategori_barang', 'bhp')
            ->map(fn (PerolehanBarangDetail $detail) => self::jenisBarangLabel($detail))
            ->unique()
            ->values()
            ->join(', ') ?: '-';
        $assetDetails = $record->details->where('kategori_barang', 'aset');
        $jenisAset = '-';
        $umurEkonomis = '-';
        $nilaiResidu = '-';

        if ($assetDetails->isNotEmpty()) {
            $jenisAset = $assetDetails
                ->map(fn (PerolehanBarangDetail $detail) => self::jenisBarangLabel($detail))
                ->unique()
                ->values()
                ->join(', ');
            $umurEkonomis = $assetDetails
                ->pluck('umur_ekonomis')
                ->filter(fn ($value) => filled($value))
                ->map(fn ($value) => ((int) $value) . ' Tahun')
                ->unique()
                ->values()
                ->join(', ') ?: '-';
            $nilaiResidu = $assetDetails
                ->pluck('nilai_residu')
                ->map(fn ($value) => self::rupiah($value))
                ->unique()
                ->values()
                ->join(', ');
        }

        return new HtmlString(<<<HTML
            <style>
                .perolehan-detail {
                    display: grid;
                    gap: 18px;
                    color: rgb(17 24 39);
                }

                .perolehan-detail__meta {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 12px 34px;
                    padding: 22px 26px;
                    border: 1px solid rgb(224 242 254);
                    border-radius: 10px;
                    background: rgb(248 250 252);
                }

                .perolehan-detail__label {
                    font-size: 13px;
                    font-weight: 600;
                    color: rgb(75 85 99);
                }

                .perolehan-detail__value {
                    margin-top: 4px;
                    font-size: 15px;
                    font-weight: 600;
                    color: rgb(17 24 39);
                }

                .perolehan-detail__table-wrap {
                    overflow-x: auto;
                    border: 1px solid rgb(226 232 240);
                    border-radius: 8px;
                    background: white;
                }

                .perolehan-detail table {
                    width: 100%;
                    min-width: 860px;
                    border-collapse: collapse;
                    font-size: 13px;
                }

                .perolehan-detail th {
                    padding: 11px 12px;
                    background: rgb(224 242 254);
                    border-bottom: 1px solid rgb(203 213 225);
                    text-align: center;
                    font-weight: 700;
                    white-space: nowrap;
                }

                .perolehan-detail td {
                    padding: 11px 12px;
                    border-bottom: 1px solid rgb(241 245 249);
                    vertical-align: top;
                }

                .perolehan-detail tbody tr:last-child td {
                    border-bottom: 0;
                }

                .perolehan-detail tfoot td {
                    background: rgb(240 253 244);
                    font-weight: 700;
                    border-top: 1px solid rgb(187 247 208);
                }

                .perolehan-detail .ta-center {
                    text-align: center;
                }

                .perolehan-detail .ta-number {
                    text-align: right;
                    white-space: nowrap;
                }

                .perolehan-detail .ta-name {
                    min-width: 280px;
                    max-width: 360px;
                    white-space: normal;
                    word-break: break-word;
                }

                .perolehan-detail .ta-empty {
                    padding: 18px;
                    text-align: center;
                    color: rgb(100 116 139);
                }

                @media print {
                    body * {
                        visibility: hidden !important;
                    }

                    .perolehan-detail,
                    .perolehan-detail * {
                        visibility: visible !important;
                    }

                    .perolehan-detail {
                        position: absolute;
                        inset: 0 auto auto 0;
                        width: 100%;
                        padding: 0;
                        gap: 14px;
                        color: black;
                    }

                    .perolehan-detail__meta {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        padding: 14px 16px;
                        border-color: rgb(203 213 225);
                        background: white;
                        break-inside: avoid;
                    }

                    .perolehan-detail__table-wrap {
                        overflow: visible;
                        border-color: rgb(203 213 225);
                        break-inside: avoid;
                    }

                    .perolehan-detail table {
                        min-width: 0;
                        font-size: 11px;
                    }

                    .perolehan-detail th {
                        background: rgb(229 231 235) !important;
                        color: black;
                        padding: 8px;
                    }

                    .perolehan-detail td {
                        padding: 8px;
                    }

                    .perolehan-detail tfoot td {
                        background: rgb(243 244 246) !important;
                    }
                }
            </style>

            <div class="perolehan-detail">
                <div class="perolehan-detail__meta">
                    <div>
                        <div class="perolehan-detail__label">Nomor Perolehan</div>
                        <div class="perolehan-detail__value">{$record->id_perolehan_barang}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Sumber Perolehan</div>
                        <div class="perolehan-detail__value">{$sumberLabel}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">{$tanggalPerolehanLabel}</div>
                        <div class="perolehan-detail__value">{$tanggal}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Tanggal Diterima</div>
                        <div class="perolehan-detail__value">{$tanggalSiap}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Sumber Hibah</div>
                        <div class="perolehan-detail__value">{$namaPemberiHibah}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Kategori Barang</div>
                        <div class="perolehan-detail__value">{$kategoriBarang}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Jenis Aset</div>
                        <div class="perolehan-detail__value">{$jenisAset}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Jenis Barang Habis Pakai</div>
                        <div class="perolehan-detail__value">{$jenisBhp}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Umur Ekonomis (Tahun)</div>
                        <div class="perolehan-detail__value">{$umurEkonomis}</div>
                    </div>
                    <div>
                        <div class="perolehan-detail__label">Nilai Residu</div>
                        <div class="perolehan-detail__value">{$nilaiResidu}</div>
                    </div>
                </div>

                <div class="perolehan-detail__table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th class="ta-number">Jumlah</th>
                                <th class="ta-number">Harga Satuan</th>
                                <th class="ta-number">Total Harga</th>
                                <th class="ta-number">Alokasi Diskon</th>
                                <th class="ta-number">Alokasi Biaya Lainnya</th>
                                <th class="ta-number">Harga Perolehan</th>
                                <th class="ta-number">Total Harga Perolehan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$rows}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" class="ta-number">Grand Total</td>
                                <td class="ta-number">{$totalFormatted}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        HTML);
    }

    private static function kategoriBarangLabel(?string $kategori): string
    {
        return match ($kategori) {
            'aset' => 'Aset Tetap',
            'bhp' => 'Barang Habis Pakai',
            default => '-',
        };
    }

    private static function jenisBarangLabel(PerolehanBarangDetail $detail): string
    {
        if ($detail->kategori_barang === 'bhp') {
            return match ($detail->jenis_bhp) {
                'atk_operasional_kantor' => 'ATK Operasional Kantor',
                'inventaris_kantor' => 'BPP Inventaris Kantor',
                default => 'Barang Habis Pakai',
            };
        }

        return match ($detail->jenis_aset) {
            'sarana_pendidikan_laboratorium' => 'Sarana Pendidikan Laboratorium',
            'inventaris_kantor' => 'Inventaris Kantor',
            'kendaraan' => 'Kendaraan',
            default => 'Aset Tetap',
        };
    }

    private static function sumberPerolehanLabel(?string $sumber): string
    {
        return match ($sumber) {
            PerolehanBarang::SUMBER_HIBAH_LEGACY,
            PerolehanBarang::SUMBER_HIBAH => 'Hibah Barang',
            PerolehanBarang::SUMBER_HIBAH_UANG => 'Hibah Uang',
            default => 'Pembelian',
        };
    }

    private static function rupiah(mixed $value): string
    {
        return 'Rp' . number_format((int) $value, 0, ',', '.');
    }

    private static function grandTotalForTable(mixed $record): int
    {
        $sumberPerolehan = $record->sumber_perolehan ?? null;
        $grandTotal = (int) ($record->grand_total ?? 0);

        if (in_array($sumberPerolehan, [
            PerolehanBarang::SUMBER_HIBAH_LEGACY,
            PerolehanBarang::SUMBER_HIBAH,
            PerolehanBarang::SUMBER_HIBAH_UANG,
        ], true)) {
            $totalNilaiHibah = (int) ($record->total_nilai_hibah ?? 0);
            $totalDetail = $record instanceof PerolehanBarang
                ? (int) $record->details->sum('total_harga_perolehan')
                : 0;

            return $grandTotal ?: $totalNilaiHibah ?: $totalDetail;
        }

        return $grandTotal;
    }

}
