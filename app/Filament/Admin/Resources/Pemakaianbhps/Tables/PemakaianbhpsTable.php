<?php

namespace App\Filament\Admin\Resources\Pemakaianbhps\Tables;

use App\Models\BarangKantor;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\DB;

class PemakaianbhpsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('lihat')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id_pemakaian')
                    ->label('No')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang BHP')
                    ->state(fn ($record) => $record->nama_barang ?? $record->barang?->nama_barang)
                    ->searchable()
                    ->description(fn ($record) => $record->kode_barang),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . ($record->barang?->satuan ?: ''))
                    ->badge()
                    ->color('info'),

                TextColumn::make('stok_saat_ini')
                    ->label('Stok')
                    ->getStateUsing(fn ($record) => $record->barang?->stok ?? 0)
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . ($record->barang?->satuan ?: ''))
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),

                TextColumn::make('tanggal_pemakaian')
                    ->label('Tanggal Penggunaan')
                    ->date('d/m/Y')
                    ->sortable(),

                ImageColumn::make('bukti_pendukung')
                    ->label('Bukti')
                    ->disk('public')
                    ->height(180)
                    ->extraImgAttributes([
                        'style' => 'object-fit: contain; width: 180px; height: 180px; border-radius: 18px; display: block; margin: 0 auto; background: #ffffff; border: 1px solid #dbeafe; padding: 8px;',
                    ])
                    ->width('32%')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'   => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    }),
            ])
            ->actionsColumnLabel('Approval')
            ->filters([
                Filter::make('pencarian_data')
                    ->label('Pencarian Data')
                    ->columns(1)
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->placeholder('Semua status')
                            ->options([
                                'pending' => 'Pending',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->searchable(),
                        Select::make('user_id')
                            ->label('Nama Pengguna')
                            ->placeholder('Semua pengguna')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id_user')
                                ->all())
                            ->searchable(),
                        Select::make('kode_barang')
                            ->label('Nama Barang BHP')
                            ->placeholder('Semua barang')
                            ->options(fn (): array => BarangKantor::query()
                                ->where('kategori_barang', 'bhp')
                                ->orderBy('nama_barang')
                                ->get()
                                ->mapWithKeys(fn (BarangKantor $barang) => [
                                    $barang->kode_barang => "{$barang->nama_barang} ({$barang->kode_barang})",
                                ])
                                ->all())
                            ->searchable(),
                        Select::make('periode')
                            ->label('Periode Cepat')
                            ->options([
                                'hari_ini' => 'Hari Ini',
                                '7_hari' => '7 Hari Terakhir',
                                '30_hari' => '30 Hari Terakhir',
                                'bulan_ini' => 'Bulan Ini',
                            ])
                            ->placeholder('Pilih periode'),
                    ])
                    ->query(function ($query, array $data) {
                        $tanggalAwal = $data['tanggal_awal'] ?? null;
                        $tanggalAkhir = $data['tanggal_akhir'] ?? null;

                        if (($data['periode'] ?? null) === 'hari_ini') {
                            $tanggalAwal = now()->toDateString();
                            $tanggalAkhir = now()->toDateString();
                        } elseif (($data['periode'] ?? null) === '7_hari') {
                            $tanggalAwal = now()->subDays(6)->toDateString();
                            $tanggalAkhir = now()->toDateString();
                        } elseif (($data['periode'] ?? null) === '30_hari') {
                            $tanggalAwal = now()->subDays(29)->toDateString();
                            $tanggalAkhir = now()->toDateString();
                        } elseif (($data['periode'] ?? null) === 'bulan_ini') {
                            $tanggalAwal = now()->startOfMonth()->toDateString();
                            $tanggalAkhir = now()->endOfMonth()->toDateString();
                        }

                        if ($tanggalAwal && $tanggalAkhir && $tanggalAkhir < $tanggalAwal) {
                            $tanggalAkhir = $tanggalAwal;
                        }

                        return $query
                            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                            ->when($data['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
                            ->when($data['kode_barang'] ?? null, fn ($query, $kodeBarang) => $query->where('kode_barang', $kodeBarang))
                            ->when($tanggalAwal, fn ($query) => $query->whereDate('tanggal_pemakaian', '>=', $tanggalAwal))
                            ->when($tanggalAkhir, fn ($query) => $query->whereDate('tanggal_pemakaian', '<=', $tanggalAkhir));
                    }),
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
                    ->modalHeading(fn ($record) => 'Lihat ' . ($record->nama_barang ?? $record->barang?->nama_barang ?? 'Pengajuan'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->fillForm(fn ($record) => [
                        'kode_barang' => $record->kode_barang,
                        'nama_barang' => $record->nama_barang ?? $record->barang?->nama_barang,
                        'nama_pengguna' => $record->user?->name,
                        'jumlah' => $record->jumlah . ' ' . ($record->barang?->satuan ?: ''),
                        'tanggal_pemakaian' => \Carbon\Carbon::parse($record->tanggal_pemakaian)->format('Y-m-d'),
                        'status' => $record->status,
                        'alasan_kebutuhan' => $record->alasan_kebutuhan,
                    ])
                    ->form([
                        TextInput::make('kode_barang')->label('Kode Barang'),
                        TextInput::make('nama_barang')->label('Nama Barang'),
                        TextInput::make('nama_pengguna')->label('Nama Pengguna'),
                        TextInput::make('jumlah')->label('Jumlah'),
                        TextInput::make('tanggal_pemakaian')->label('Tanggal Pemakaian'),
                        TextInput::make('status')->label('Status'),
                        Textarea::make('alasan_kebutuhan')->label('Alasan Kebutuhan')->rows(4),
                    ])
                    ->disabledForm(),
                // =============================
                // SETUJUI
                // =============================
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Permintaan?')
                    ->modalDescription('Stok barang akan dikurangi sesuai jumlah yang diminta.')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            $barang = BarangKantor::where('kode_barang', $record->kode_barang)
                                ->lockForUpdate()
                                ->first();

                            if (! $barang || $barang->stok < $record->jumlah) {
                                Notification::make()
                                    ->title('Gagal!')
                                    ->body('Stok tidak mencukupi atau barang tidak ditemukan.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $barang->decrement('stok', $record->jumlah);
                            $record->update(['status' => 'disetujui']);

                            Notification::make()
                                ->title('Berhasil')
                                ->body("Permintaan disetujui, stok {$barang->nama_barang} telah dikurangi.")
                                ->success()
                                ->send();
                        });
                    }),

                // =============================
                // TOLAK
                // =============================
                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Permintaan?')
                    ->modalDescription('Permintaan akan ditandai sebagai ditolak.')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update(['status' => 'ditolak']);

                        Notification::make()
                            ->title('Permintaan Ditolak')
                            ->body('Permintaan telah ditolak.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada pemakaian BHP')
            ->emptyStateDescription('Data pemakaian BHP akan tampil setelah ada permintaan pemakaian.');
    }
}
