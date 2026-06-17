<?php

namespace App\Filament\Admin\Resources\Penyusutans\Tables;

use App\Filament\Admin\Resources\Penyusutans\PenyusutanResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PenyusutansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('lihat')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id_penyusutan')
                    ->label('ID Penyusutan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama_aset')
                    ->label('Nama Aset')
                    ->html()
                    ->wrap()
                    ->formatStateUsing(fn ($state) => "<div style='max-width:280px;white-space:normal;word-break:break-word;line-height:1.7'>" . e($state) . "</div>")
                    ->width('280px')
                    ->extraAttributes([
                        'style' => 'width:280px;max-width:280px;white-space:normal;vertical-align:top;',
                    ])
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status_penggunaan')
                    ->label('Status Penggunaan')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'belum_siap_digunakan' => 'warning',
                        'siap_digunakan' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'belum_siap_digunakan' => 'Belum Siap Digunakan',
                        'siap_digunakan' => 'Siap Digunakan',
                        default => '-',
                    }),

                TextColumn::make('status_penyusutan')
                    ->label('Status Penyusutan')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Aktif' => 'success',
                        'Tidak Aktif' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ?? '-'),

                TextColumn::make('harga_perolehan')
                    ->label('Harga Perolehan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('beban_penyusutan_bulanan')
                    ->label('Beban / Bulan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('total_biaya_penyusutan')
                    ->label('Total Biaya Penyusutan Aset Tetap')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('akhir_umur_ekonomis')
                    ->label('Berakhir Pada')
                    ->getStateUsing(fn ($record) => $record->isSiapPakai()
                        ? $record->bulanAkhirUmurEkonomis()->translatedFormat('F Y')
                        : '-')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status_bulan_ini')
                    ->label('Status Periode Dipilih')
                    ->getStateUsing(function ($record, $livewire) {
                        $filters = $livewire->tableFilters ?? [];

                        $bulan = (int) ($filters['filter_periode']['bulan'] ?? now()->month);
                        $tahun = (int) ($filters['filter_periode']['tahun'] ?? now()->year);

                        $targetDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

                        if (! $record->isSiapPakai()) {
                            return 'Belum Siap Digunakan';
                        }

                        $akhirUmur = $record->bulanAkhirUmurEkonomis();
                        $mulai = $record->bulanMulaiPenyusutan();

                        if ($targetDate->lt($mulai)) {
                            return 'Belum Beroperasi';
                        }

                        if ($targetDate->gt($akhirUmur)) {
                            return 'Sudah Lunas';
                        }

                        if (! self::periodeSudahBolehDiposting($targetDate)) {
                            return 'Belum Waktunya';
                        }

                        $sudah = $record->details()
                            ->where('periode', $targetDate->toDateString())
                            ->exists();

                        return $sudah ? 'Sudah Disusutkan' : 'Belum Disusutkan';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Sudah Disusutkan' => 'success',
                        'Belum Disusutkan' => 'danger',
                        'Belum Waktunya' => 'info',
                        'Belum Siap Digunakan' => 'warning',
                        'Belum Beroperasi' => 'gray',
                        'Sudah Lunas' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('keterangan_kelengkapan')
                    ->label('Keterangan Kelengkapan')
                    ->getStateUsing(fn ($record) => $record->buildKeteranganKelengkapan())
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_starts_with((string) $state, 'Lengkap') => 'success',
                        str_starts_with((string) $state, 'Belum Waktunya') => 'info',
                        default => 'danger',
                    })
                    ->wrap(),
            ])
            ->filters([
                Filter::make('filter_periode')
                    ->label('Periode & Status')
                    ->form([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default((int) now()->month),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(
                                collect(range(2021, now()->year))
                                    ->mapWithKeys(fn ($y) => [$y => $y])
                                    ->toArray()
                            )
                            ->default((int) now()->year),

                        Select::make('status_penyusutan')
                            ->label('Status Aset')
                            ->placeholder('Semua status aset')
                            ->options([
                                'Aktif' => 'Aktif',
                                'Tidak Aktif' => 'Tidak Aktif',
                            ]),

                        Select::make('status_periode')
                            ->label('Status Penyusutan')
                            ->placeholder('Semua status penyusutan')
                            ->options([
                                'Sudah Disusutkan' => 'Sudah Disusutkan',
                                'Belum Disusutkan' => 'Belum Disusutkan',
                                'Belum Waktunya' => 'Belum Waktunya',
                                'Belum Siap Digunakan' => 'Belum Siap Digunakan',
                                'Belum Beroperasi' => 'Belum Beroperasi',
                                'Sudah Lunas' => 'Sudah Lunas',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $bulan = (int) ($data['bulan'] ?? now()->month);
                        $tahun = (int) ($data['tahun'] ?? now()->year);
                        $targetDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

                        $query->when(
                            $data['status_penyusutan'] ?? null,
                            fn (Builder $query, $statusPenyusutan) => $query->where('status_penyusutan', $statusPenyusutan)
                        );

                        return $query->when($data['status_periode'] ?? null, function (Builder $query, $statusPeriode) use ($targetDate) {
                            $targetDateString = $targetDate->toDateString();
                            $periodeSudahBolehDiposting = self::periodeSudahBolehDiposting($targetDate);

                            return match ($statusPeriode) {
                                'Sudah Disusutkan' => $query
                                    ->whereRaw(self::mulaiPenyusutanSql() . ' <= ?', [$targetDateString])
                                    ->whereRaw(self::akhirPenyusutanSql() . ' >= ?', [$targetDateString])
                                    ->whereHas('details', fn (Builder $detailQuery) => $detailQuery->whereDate('periode', $targetDateString)),

                                'Belum Disusutkan' => ! $periodeSudahBolehDiposting
                                    ? $query->whereRaw('1 = 0')
                                    : $query
                                        ->whereRaw(self::mulaiPenyusutanSql() . ' <= ?', [$targetDateString])
                                        ->whereRaw(self::akhirPenyusutanSql() . ' >= ?', [$targetDateString])
                                        ->whereDoesntHave('details', fn (Builder $detailQuery) => $detailQuery->whereDate('periode', $targetDateString)),

                                'Belum Waktunya' => $periodeSudahBolehDiposting
                                    ? $query->whereRaw('1 = 0')
                                    : $query
                                        ->whereRaw(self::mulaiPenyusutanSql() . ' <= ?', [$targetDateString])
                                        ->whereRaw(self::akhirPenyusutanSql() . ' >= ?', [$targetDateString]),

                                'Belum Siap Digunakan' => $query->where(function (Builder $query): void {
                                    $query->whereNull('tanggal_diterima')
                                        ->orWhere('status_penggunaan', 'belum_siap_digunakan');
                                }),

                                'Belum Beroperasi' => $query->whereRaw(self::mulaiPenyusutanSql() . ' > ?', [$targetDateString]),

                                'Sudah Lunas' => $query->whereRaw(
                                    self::akhirPenyusutanSql() . ' < ?',
                                    [$targetDateString]
                                ),

                                default => $query,
                            };
                        });
                    }),
            ])
            ->actions([
                Action::make('lihat')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Lihat ' . ($record->nama_aset ?? $record->kode_barang ?? 'Penyusutan'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->fillForm(fn ($record) => [
                        'id_penyusutan' => $record->id_penyusutan,
                        'kode_barang' => $record->kode_barang,
                        'nama_aset' => $record->nama_aset,
                        'tanggal_diterima' => $record->tanggal_diterima
                            ? Carbon::parse($record->tanggal_diterima)->format('d/m/Y')
                            : '-',
                        'harga_perolehan' => number_format((int) $record->harga_perolehan, 0, ',', '.'),
                        'beban_penyusutan_bulanan' => number_format((int) $record->beban_penyusutan_bulanan, 0, ',', '.'),
                        'total_biaya_penyusutan' => number_format((int) $record->total_biaya_penyusutan, 0, ',', '.'),
                        'status_penyusutan' => strtoupper((string) ($record->status_penyusutan ?? '-')),
                    ])
                    ->form([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('id_penyusutan')
                                    ->label('ID Penyusutan'),

                                TextInput::make('kode_barang')
                                    ->label('Kode Barang'),

                                TextInput::make('nama_aset')
                                    ->label('Nama Aset'),

                                TextInput::make('tanggal_diterima')
                                    ->label('Tanggal Diterima'),

                                TextInput::make('harga_perolehan')
                                    ->label('Harga Perolehan')
                                    ->prefix('Rp')
                                    ->extraInputAttributes(['style' => 'text-align: right']),

                                TextInput::make('beban_penyusutan_bulanan')
                                    ->label('Beban / Bulan')
                                    ->prefix('Rp')
                                    ->extraInputAttributes(['style' => 'text-align: right']),

                                TextInput::make('total_biaya_penyusutan')
                                    ->label('Total Biaya Penyusutan')
                                    ->prefix('Rp')
                                    ->extraInputAttributes(['style' => 'text-align: right']),

                                TextInput::make('status_penyusutan')
                                    ->label('Status Penyusutan'),
                            ]),
                    ])
                    ->disabledForm(),

                Action::make('kartu')
                    ->label('Kartu')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn ($record) => PenyusutanResource::getUrl('kartu', ['record' => $record])),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada data penyusutan')
            ->emptyStateDescription('Data penyusutan akan tampil setelah aset siap digunakan.');
    }

    protected static function periodeSudahBolehDiposting(Carbon $targetDate): bool
    {
        return now()->gte($targetDate->copy()->startOfDay());
    }

    private static function mulaiPenyusutanSql(): string
    {
        return "CASE WHEN tanggal_diterima IS NULL THEN NULL WHEN DAY(tanggal_diterima) > 15 THEN DATE_ADD(DATE_FORMAT(tanggal_diterima, '%Y-%m-01'), INTERVAL 1 MONTH) ELSE DATE_FORMAT(tanggal_diterima, '%Y-%m-01') END";
    }

    private static function akhirPenyusutanSql(): string
    {
        return 'LAST_DAY(DATE_ADD(' . self::mulaiPenyusutanSql() . ', INTERVAL ((umur_ekonomis_tahun * 12) - 1) MONTH))';
    }

}
