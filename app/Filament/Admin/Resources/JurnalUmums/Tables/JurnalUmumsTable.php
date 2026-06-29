<?php

namespace App\Filament\Admin\Resources\JurnalUmums\Tables;

use App\Models\PerolehanBarang;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class JurnalUmumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with([
                    'perolehanBarang',
                    'penyusutan',
                    'pengisianKasKecil',
                    'pendapatanHibah',
                    'perolehanBarang.details.barangKantor',
                    'details.coa',
                ]);
            })
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('nomor_bukti_tampil')
                    ->label('Nomor Bukti')
                    ->state(function ($record) {
                        return $record->tipe_transaksi === 'penyusutan'
                            ? ($record->reff_penyusutan ?? '-')
                            : ($record->reff_perolehan_barang
                                ?? $record->reff_pengisian_kas_kecil
                                ?? $record->reff_pendapatan_hibah
                                ?? '-');
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('reff_perolehan_barang', 'like', "%{$search}%")
                                ->orWhere('reff_penyusutan', 'like', "%{$search}%")
                                ->orWhere('reff_pengisian_kas_kecil', 'like', "%{$search}%")
                                ->orWhere('reff_pendapatan_hibah', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "COALESCE(reff_penyusutan, reff_perolehan_barang, reff_pengisian_kas_kecil, reff_pendapatan_hibah) {$direction}"
                        );
                    }),

                TextColumn::make('deskripsi_tampil')
                    ->label('Deskripsi')
                    ->state(function ($record) {
                        if (in_array($record->tipe_transaksi, ['pembelian_barang', 'perolehan_barang'], true) && $record->perolehanBarang) {
                            $keterangan = $record->perolehanBarang->keterangan
                                ?? $record->perolehanBarang->deskripsi
                                ?? null;

                            if ($keterangan) {
                                return $keterangan;
                            }

                            if (method_exists($record->perolehanBarang, 'details') && $record->perolehanBarang->relationLoaded('details')) {
                                $details = $record->perolehanBarang->details;

                                if ($details && $details->count() > 0) {
                                    $firstName = $details->first()->barangKantor->nama_barang
                                        ?? $details->first()->nama_barang
                                        ?? null;

                                    $cnt = $details->count();

                                    if ($firstName) {
                                        $label = match ($record->perolehanBarang->sumber_perolehan) {
                                            PerolehanBarang::SUMBER_HIBAH_LEGACY,
                                            PerolehanBarang::SUMBER_HIBAH => 'Hibah Barang',
                                            PerolehanBarang::SUMBER_HIBAH_UANG => 'Hibah Uang',
                                            default => 'Perolehan',
                                        };

                                        return $cnt > 1
                                            ? "{$label} {$firstName} (+" . ($cnt - 1) . ' item)'
                                            : "{$label} {$firstName}";
                                    }

                                    return "Perolehan ({$cnt} item)";
                                }
                            }

                            return $record->deskripsi ?: 'Perolehan';
                        }

                        if ($record->tipe_transaksi === 'pengisian_kas_kecil') {
                            return $record->deskripsi ?: 'Pengisian kas kecil';
                        }

                        if ($record->tipe_transaksi === 'pendapatan_hibah') {
                            return $record->deskripsi ?: 'Pendapatan hibah';
                        }

                        return $record->deskripsi;
                    })
                    ->searchable()
                    ->limit(50),

                TextColumn::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'perolehan_barang' => 'Perolehan Barang',
                        'pembelian_barang' => 'Perolehan Barang',
                        'penyusutan' => 'Penyusutan',
                        'pengisian_kas_kecil' => 'Pengisian Kas Kecil',
                        'pendapatan_hibah' => 'Pendapatan Hibah',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'perolehan_barang' => 'success',
                        'pembelian_barang' => 'success',
                        'penyusutan' => 'warning',
                        'pengisian_kas_kecil' => 'info',
                        'pendapatan_hibah' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->recordAction('lihat')
            ->recordUrl(null)
            ->actions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Lihat ' . ($record->reff_transaksi ?: 'Jurnal Umum'))
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn ($record) => self::jurnalUmumContent($record)),
            ])
            ->bulkActions([])
            ->defaultSort('tanggal', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada jurnal umum')
            ->emptyStateDescription('Jurnal akan tampil setelah transaksi diposting.');
    }

    private static function jurnalUmumContent($record): HtmlString
    {
        $record->loadMissing('details.coa');

        $tanggal = $record->tanggal ? \Carbon\Carbon::parse($record->tanggal)->format('d/m/Y') : '-';
        $reff = e($record->reff_transaksi ?: '-');
        $tipe = e(match ($record->tipe_transaksi) {
            'perolehan_barang', 'pembelian_barang' => 'Perolehan Barang',
            'penyusutan' => 'Penyusutan',
            'pengisian_kas_kecil' => 'Pengisian Kas Kecil',
            'pendapatan_hibah' => 'Pendapatan Hibah',
            default => ucfirst($record->tipe_transaksi ?: '-'),
        });
        $deskripsi = e($record->deskripsi ?: '-');

        $rows = $record->details->map(function ($detail): string {
            return '<tr>'
                . '<td>' . e($detail->coa?->kode_akun ?: '-') . '</td>'
                . '<td>' . e($detail->coa?->nama_akun ?: '-') . '</td>'
                . '<td class="ju-detail-number">Rp' . number_format((int) $detail->nominal_debit, 0, ',', '.') . '</td>'
                . '<td class="ju-detail-number">Rp' . number_format((int) $detail->nominal_kredit, 0, ',', '.') . '</td>'
                . '</tr>';
        })->join('') ?: '<tr><td colspan="4" class="ju-detail-empty">Tidak ada detail jurnal.</td></tr>';

        return new HtmlString(<<<HTML
            <style>
                .ju-detail { display: grid; gap: 18px; color: rgb(17 24 39); }
                .ju-detail-header { text-align: center; padding-bottom: 12px; border-bottom: 2px solid rgb(14 165 233); }
                .ju-detail-header h2 { margin: 0; font-size: 26px; line-height: 1.2; font-weight: 800; color: rgb(7 89 133); letter-spacing: 0; }
                .ju-detail-header h3 { margin: 6px 0 0; font-size: 17px; font-weight: 700; color: rgb(2 132 199); }
                .ju-detail-header p { margin: 4px 0 0; font-size: 13px; color: rgb(71 85 105); }
                .ju-detail-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 28px; padding: 18px 20px; background: rgb(248 250 252); border: 1px solid rgb(224 242 254); border-radius: 10px; }
                .ju-detail-label { font-size: 13px; font-weight: 600; color: rgb(75 85 99); }
                .ju-detail-value { margin-top: 3px; font-size: 15px; font-weight: 700; color: rgb(17 24 39); }
                .ju-detail table { width: 100%; border-collapse: collapse; font-size: 13px; }
                .ju-detail th { padding: 10px 12px; background: rgb(224 242 254); border: 1px solid rgb(203 213 225); text-align: center; font-weight: 700; }
                .ju-detail td { padding: 10px 12px; border: 1px solid rgb(226 232 240); }
                .ju-detail-number { text-align: right; white-space: nowrap; }
                .ju-detail-empty { text-align: center; color: rgb(100 116 139); }
                @media print {
                    body * { visibility: hidden !important; }
                    .ju-detail, .ju-detail * { visibility: visible !important; }
                    .ju-detail { position: absolute; inset: 0 auto auto 0; width: 100%; color: black; gap: 14px; }
                    .ju-detail-header h2, .ju-detail-header h3, .ju-detail-header p { color: black; }
                    .ju-detail-meta { background: white; border-color: rgb(203 213 225); break-inside: avoid; }
                    .ju-detail th { background: rgb(229 231 235) !important; color: black; }
                }
            </style>
            <div class="ju-detail">
                <div class="ju-detail-header">
                    <h2>CoE SMART EV</h2>
                    <h3>Jurnal Umum</h3>
                    <p>{$reff} | {$tanggal}</p>
                </div>
                <div class="ju-detail-meta">
                    <div><div class="ju-detail-label">Nomor Bukti</div><div class="ju-detail-value">{$reff}</div></div>
                    <div><div class="ju-detail-label">Tanggal</div><div class="ju-detail-value">{$tanggal}</div></div>
                    <div><div class="ju-detail-label">Tipe Transaksi</div><div class="ju-detail-value">{$tipe}</div></div>
                    <div><div class="ju-detail-label">Deskripsi</div><div class="ju-detail-value">{$deskripsi}</div></div>
                </div>
                <table>
                    <thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Debit</th><th>Kredit</th></tr></thead>
                    <tbody>{$rows}</tbody>
                </table>
            </div>
        HTML);
    }
}

