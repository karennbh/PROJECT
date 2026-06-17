<?php

namespace App\Filament\Admin\Resources\PeminjamanBarangs\Tables;

use App\Models\BarangKantor;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PeminjamanBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('lihat')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id_peminjaman')
                    ->label('No')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->state(fn ($record) => $record->nama_barang ?? $record->barang?->nama_barang)
                    ->searchable()
                    ->description(fn ($record) => $record->kode_barang)
                    ->limit(30),

                TextColumn::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'aset' => 'Aset Tetap',
                        'bhp' => 'Barang Habis Pakai',
                        default => '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'aset' => 'info',
                        'bhp' => 'success',
                        default => 'gray',
                    })
                    ->placeholder('-'),

                TextColumn::make('jumlah_pinjam')
                    ->label('Jumlah')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('user.name')
                    ->label('Nama Peminjam')
                    ->searchable(),

                TextColumn::make('tanggal_pinjam')
                    ->label('Tanggal Pinjam')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tanggal_pengembalian')
                    ->label('Tanggal Kembali')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->is_terlambat
                        ? 'Telat ' . $record->hari_terlambat . ' hari'
                        : null)
                    ->color(fn ($record) => $record->is_terlambat ? 'danger' : 'gray'),

                TextColumn::make('alasan_peminjaman')
                    ->label('Keterangan')
                    ->limit(35)
                    ->wrap()
                    ->placeholder('-'),

                ImageColumn::make('bukti_peminjaman')
                    ->label('Bukti Pinjam')
                    ->disk('public')
                    ->height(180)
                    ->extraImgAttributes([
                        'style' => 'object-fit: contain; width: 180px; height: 180px; border-radius: 18px; display: block; margin: 0 auto; background: #ffffff; border: 1px solid #dbeafe; padding: 8px;',
                    ])
                    ->width('32%')
                    ->alignCenter(),

                ImageColumn::make('bukti_pengembalian')
                    ->label('Bukti Kembali')
                    ->disk('public')
                    ->height(180)
                    ->extraImgAttributes([
                        'style' => 'object-fit: contain; width: 180px; height: 180px; border-radius: 18px; display: block; margin: 0 auto; background: #ffffff; border: 1px solid #dbeafe; padding: 8px;',
                    ])
                    ->width('32%')
                    ->alignCenter(),

                TextColumn::make('status_pinjam')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state, $record) => match ($state) {
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'menunggu_verifikasi_pengembalian' => 'warning',
                        'disetujui' => $record->is_terlambat ? 'danger' : 'primary',
                        'kembali' => 'success',
                        'ditolak' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state, $record) => $record->is_terlambat
                        ? 'TERLAMBAT'
                        : match ($state) {
                            'expired' => 'Expired',
                            'menunggu_verifikasi_pengembalian' => 'Menunggu Verifikasi Admin',
                            'disetujui' => 'Dipinjam',
                            'kembali' => 'Selesai',
                            default => ucfirst($state),
                        }),
            ])
            ->actionsColumnLabel('Approval')
            ->filters([
                Filter::make('pencarian_data')
                    ->label('Pencarian Data')
                    ->columns(1)
                    ->form([
                        Select::make('status_pinjam')
                            ->label('Status Peminjaman Barang Kantor')
                            ->placeholder('Semua status')
                            ->options([
                                'pending' => 'Pending',
                                'expired' => 'Expired',
                                'disetujui' => 'Sedang Dipinjam',
                                'menunggu_verifikasi_pengembalian' => 'Menunggu Verifikasi Admin',
                                'kembali' => 'Sudah Dikembalikan',
                                'ditolak' => 'Ditolak',
                            ])
                            ->searchable(),
                        Select::make('user_id')
                            ->label('Nama Peminjam')
                            ->placeholder('Semua peminjam')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->pluck('name', 'id_user')
                                ->all())
                            ->searchable(),
                        Select::make('kategori_barang')
                            ->label('Kategori Barang')
                            ->placeholder('Semua kategori')
                            ->options([
                                'aset' => 'Aset Tetap',
                                'bhp' => 'Barang Habis Pakai',
                            ]),
                        Select::make('kode_barang')
                            ->label('Nama Barang')
                            ->placeholder('Semua barang')
                            ->options(fn (): array => BarangKantor::query()
                                ->borrowableForPeminjaman()
                                ->orderByRaw("FIELD(kategori_barang, 'aset', 'bhp')")
                                ->orderBy('nama_barang')
                                ->get()
                                ->mapWithKeys(fn (BarangKantor $barang) => [
                                    $barang->kode_barang => "{$barang->nama_barang} ({$barang->kode_barang}) - {$barang->jenis_barang_label}",
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
                        $periode = $data['periode'] ?? null;

                        if ($periode === 'hari_ini') {
                            $tanggalAwal = now()->toDateString();
                            $tanggalAkhir = now()->toDateString();
                        } elseif ($periode === '7_hari') {
                            $tanggalAwal = now()->subDays(6)->toDateString();
                            $tanggalAkhir = now()->toDateString();
                        } elseif ($periode === '30_hari') {
                            $tanggalAwal = now()->subDays(29)->toDateString();
                            $tanggalAkhir = now()->toDateString();
                        } elseif ($periode === 'bulan_ini') {
                            $tanggalAwal = now()->startOfMonth()->toDateString();
                            $tanggalAkhir = now()->endOfMonth()->toDateString();
                        }

                        if ($tanggalAwal && $tanggalAkhir && $tanggalAkhir < $tanggalAwal) {
                            $tanggalAkhir = $tanggalAwal;
                        }

                        return $query
                            ->when($data['status_pinjam'] ?? null, fn ($query, $status) => $query->where('status_pinjam', $status))
                            ->when($data['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
                            ->when($data['kategori_barang'] ?? null, fn ($query, $kategori) => $query->where('kategori_barang', $kategori))
                            ->when($data['kode_barang'] ?? null, fn ($query, $kodeBarang) => $query->where('kode_barang', $kodeBarang))
                            ->when($tanggalAwal, fn ($query) => $query->whereDate('tanggal_pinjam', '>=', $tanggalAwal))
                            ->when($tanggalAkhir, fn ($query) => $query->whereDate('tanggal_pinjam', '<=', $tanggalAkhir));
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
                        'kategori_barang' => match ($record->kategori_barang) {
                            'aset' => 'Aset Tetap',
                            'bhp' => 'Barang Habis Pakai',
                            default => '-',
                        },
                        'nama_barang' => $record->nama_barang ?? $record->barang?->nama_barang,
                        'nama_peminjam' => $record->user?->name,
                        'jumlah_pinjam' => $record->jumlah_pinjam,
                        'tanggal_pinjam' => \Carbon\Carbon::parse($record->tanggal_pinjam)->format('Y-m-d'),
                        'tanggal_pengembalian' => \Carbon\Carbon::parse($record->tanggal_pengembalian)->format('Y-m-d'),
                        'status_pinjam' => $record->status_pinjam,
                        'alasan_peminjaman' => $record->alasan_peminjaman,
                    ])
                    ->form([
                        TextInput::make('kode_barang')->label('Kode Barang'),
                        TextInput::make('kategori_barang')->label('Kategori Barang'),
                        TextInput::make('nama_barang')->label('Nama Barang'),
                        TextInput::make('nama_peminjam')->label('Nama Peminjam'),
                        TextInput::make('jumlah_pinjam')->label('Jumlah'),
                        TextInput::make('tanggal_pinjam')->label('Tanggal Pinjam'),
                        TextInput::make('tanggal_pengembalian')->label('Tanggal Kembali'),
                        TextInput::make('status_pinjam')->label('Status'),
                        Textarea::make('alasan_peminjaman')->label('Alasan')->rows(4),
                    ])
                    ->disabledForm(),
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status_pinjam === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            $barang = BarangKantor::where('kode_barang', $record->kode_barang)
                                ->lockForUpdate()
                                ->first();

                            if (! $barang || ! $barang->isAvailableToBorrow((int) $record->jumlah_pinjam)) {
                                Notification::make()
                                    ->title('Barang Tidak Tersedia')
                                    ->body('Barang sudah dipinjam, nonaktif, atau stok tidak mencukupi.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $record->update(['status_pinjam' => 'disetujui']);
                            $barang->markAsBorrowed((int) $record->jumlah_pinjam);

                            Notification::make()
                                ->title('Peminjaman Barang Kantor Disetujui')
                                ->success()
                                ->sendToDatabase($record->user);
                        });
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status_pinjam === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status_pinjam' => 'ditolak']);

                        Notification::make()
                            ->title('Peminjaman Barang Kantor Ditolak')
                            ->danger()
                            ->sendToDatabase($record->user);
                    }),

                Action::make('kembali')
                    ->label(fn ($record) => $record->status_pinjam === 'menunggu_verifikasi_pengembalian' ? 'Verifikasi Pengembalian' : 'Terima Barang')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status_pinjam, ['disetujui', 'menunggu_verifikasi_pengembalian'], true))
                    ->modalHeading(fn ($record) => $record->status_pinjam === 'menunggu_verifikasi_pengembalian'
                        ? 'Verifikasi Pengembalian'
                        : 'Terima Barang')
                    ->modalDescription(fn ($record) => $record->status_pinjam === 'menunggu_verifikasi_pengembalian'
                        ? 'Bukti pengembalian sudah diupload oleh anggota. Klik verifikasi untuk menyelesaikan proses pengembalian.'
                        : 'Upload bukti pengembalian barang untuk menyelesaikan proses pengembalian.')
                    ->modalSubmitActionLabel(fn ($record) => $record->status_pinjam === 'menunggu_verifikasi_pengembalian'
                        ? 'Verifikasi'
                        : 'Simpan dan Terima')
                    ->form([
                        FileUpload::make('bukti_pengembalian')
                            ->label('Bukti Pengembalian Barang')
                            ->disk('public')
                            ->directory('bukti_pengembalian')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                            ->maxSize(2048)
                            ->visible(fn ($record) => $record->status_pinjam !== 'menunggu_verifikasi_pengembalian')
                            ->required(fn ($record) => $record->status_pinjam !== 'menunggu_verifikasi_pengembalian')
                            ->extraInputAttributes([
                                'accept' => 'image/*',
                                'capture' => 'environment',
                            ])
                            ->helperText('Bisa upload gambar dari galeri atau foto langsung dari kamera perangkat. Bukti ini disimpan di kolom yang sama dengan upload dari anggota.'),
                    ])
                    ->action(function ($record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            $isVerificationOnly = $record->status_pinjam === 'menunggu_verifikasi_pengembalian';

                            $barang = BarangKantor::where('kode_barang', $record->kode_barang)
                                ->lockForUpdate()
                                ->first();

                            if (! $barang) {
                                Notification::make()
                                    ->title('Data barang tidak ditemukan')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $barang->markAsReturned((int) $record->jumlah_pinjam);
                            $updateData = [
                                'status_pinjam' => 'kembali',
                            ];

                            if (! $isVerificationOnly) {
                                $updateData['bukti_pengembalian'] = $data['bukti_pengembalian'];
                            }

                            $record->update($updateData);

                            Notification::make()
                                ->title('Pengembalian Berhasil Diverifikasi')
                                ->body($isVerificationOnly
                                    ? 'Pengembalian berhasil diverifikasi.'
                                    : 'Bukti pengembalian barang berhasil disimpan.')
                                ->success()
                                ->sendToDatabase($record->user);
                        });
                    }),
            ])
            ->defaultSort('id_peminjaman', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Belum ada peminjaman barang')
            ->emptyStateDescription('Data peminjaman akan tampil setelah pengguna mengajukan peminjaman.');
    }
}
