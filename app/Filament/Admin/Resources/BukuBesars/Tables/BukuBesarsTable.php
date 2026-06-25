<?php

namespace App\Filament\Admin\Resources\BukuBesars\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class BukuBesarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('details.coa'))
            ->columns([
                TextColumn::make('tanggal')->date('d M Y')->label('Tgl'),
                TextColumn::make('reff_transaksi')
                    ->label('Ref')
                    ->state(fn ($record): string => self::referenceFor($record) ?: '-'),
                TextColumn::make('deskripsi')->limit(30),

                TextColumn::make('details.nominal_debit')
                    ->label('Total Debit')
                    ->formatStateUsing(function ($state, $record) {
                        $debit = $record->details()->sum('nominal_debit');
                        return 'Rp'.number_format((int) $debit, 0, ',', '.');
                    })
                    ->alignment('end'),

                TextColumn::make('details.nominal_kredit')
                    ->label('Total Kredit')
                    ->formatStateUsing(function ($state, $record) {
                        $credit = $record->details()->sum('nominal_kredit');
                        return 'Rp'.number_format((int) $credit, 0, ',', '.');
                    })
                    ->alignment('end'),
            ])
            ->filters([
                //
            ])
            ->recordAction('lihat')
            ->recordUrl(null)
            ->recordActions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Lihat ' . (self::referenceFor($record) ?: 'Buku Besar'))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn ($record) => self::bukuBesarContent($record)),
            ])
            ->toolbarActions([])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada buku besar')
            ->emptyStateDescription('Data buku besar akan tampil setelah jurnal transaksi tersedia.');
    }

    private static function bukuBesarContent($record): HtmlString
    {
        $record->loadMissing('details.coa');

        $tanggal = $record->tanggal ? \Carbon\Carbon::parse($record->tanggal)->format('d/m/Y') : '-';
        $ref = e(self::referenceFor($record) ?: '-');
        $deskripsi = e($record->deskripsi ?: '-');

        $rows = $record->details->map(function ($detail): string {
            return '<tr>'
                . '<td>' . e($detail->coa?->kode_akun ?: '-') . '</td>'
                . '<td>' . e($detail->coa?->nama_akun ?: '-') . '</td>'
                . '<td class="bb-detail-number">Rp' . number_format((int) $detail->nominal_debit, 0, ',', '.') . '</td>'
                . '<td class="bb-detail-number">Rp' . number_format((int) $detail->nominal_kredit, 0, ',', '.') . '</td>'
                . '</tr>';
        })->join('') ?: '<tr><td colspan="4" class="bb-detail-empty">Tidak ada detail buku besar.</td></tr>';

        return new HtmlString(<<<HTML
            <style>
                .bb-detail { display: grid; gap: 18px; color: rgb(17 24 39); }
                .bb-detail-header { text-align: center; padding-bottom: 12px; border-bottom: 2px solid rgb(14 165 233); }
                .bb-detail-header h2 { margin: 0; font-size: 26px; line-height: 1.2; font-weight: 800; color: rgb(7 89 133); letter-spacing: 0; }
                .bb-detail-header h3 { margin: 6px 0 0; font-size: 17px; font-weight: 700; color: rgb(2 132 199); }
                .bb-detail-header p { margin: 4px 0 0; font-size: 13px; color: rgb(71 85 105); }
                .bb-detail-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 28px; padding: 18px 20px; background: rgb(248 250 252); border: 1px solid rgb(224 242 254); border-radius: 10px; }
                .bb-detail-label { font-size: 13px; font-weight: 600; color: rgb(75 85 99); }
                .bb-detail-value { margin-top: 3px; font-size: 15px; font-weight: 700; color: rgb(17 24 39); }
                .bb-detail table { width: 100%; border-collapse: collapse; font-size: 13px; }
                .bb-detail th { padding: 10px 12px; background: rgb(224 242 254); border: 1px solid rgb(203 213 225); text-align: center; font-weight: 700; }
                .bb-detail td { padding: 10px 12px; border: 1px solid rgb(226 232 240); }
                .bb-detail-number { text-align: right; white-space: nowrap; }
                .bb-detail-empty { text-align: center; color: rgb(100 116 139); }
                @media print {
                    body * { visibility: hidden !important; }
                    .bb-detail, .bb-detail * { visibility: visible !important; }
                    .bb-detail { position: absolute; inset: 0 auto auto 0; width: 100%; color: black; gap: 14px; }
                    .bb-detail-header h2, .bb-detail-header h3, .bb-detail-header p { color: black; }
                    .bb-detail-meta { background: white; border-color: rgb(203 213 225); break-inside: avoid; }
                    .bb-detail th { background: rgb(229 231 235) !important; color: black; }
                }
            </style>
            <div class="bb-detail">
                <div class="bb-detail-header">
                    <h2>CoE SMART EV</h2>
                    <h3>Buku Besar</h3>
                    <p>{$ref} | {$tanggal}</p>
                </div>
                <div class="bb-detail-meta">
                    <div><div class="bb-detail-label">Tanggal</div><div class="bb-detail-value">{$tanggal}</div></div>
                    <div><div class="bb-detail-label">Ref</div><div class="bb-detail-value">{$ref}</div></div>
                    <div style="grid-column: 1 / -1;"><div class="bb-detail-label">Deskripsi</div><div class="bb-detail-value">{$deskripsi}</div></div>
                </div>
                <table>
                    <thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th></tr></thead>
                    <tbody>{$rows}</tbody>
                </table>
            </div>
        HTML);
    }

    private static function referenceFor($record): ?string
    {
        return $record->reff_penyusutan
            ?: $record->reff_perolehan_barang
            ?: $record->reff_pengisian_kas_kecil
            ?: $record->reff_pendapatan_hibah
            ?: null;
    }
}
