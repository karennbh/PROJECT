<?php

namespace App\Filament\Admin\Resources\BarangKantors\Schemas;

use App\Filament\Admin\Resources\BarangKantors\BarangKantorResource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use App\Models\BarangKantor;
use App\Models\KategoriAsetTetap;
use Filament\Forms\Components\FileUpload;
use Filament\Support\RawJs;
use Carbon\Carbon;

class BarangKantorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes([
                'novalidate' => true,
            ])
            ->components([
                Select::make('kategori_barang')
                    ->label('Kategori Barang')
                    ->options([
                        'aset' => 'Aset Tetap',
                        'bhp'  => 'Barang Habis Pakai',
                    ])
                    ->markAsRequired()
                    ->rule('required')
                    ->reactive()
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->validationMessages([
                        'required' => 'Kategori barang wajib dipilih.',
                    ])
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $prefix = $state === 'aset' ? 'ASET-' : 'BHP-';

                        $last = BarangKantor::where('kategori_barang', $state)
                            ->orderByDesc('kode_barang')
                            ->value('kode_barang');

                        $nextNumber = $last ? intval(substr($last, 5)) + 1 : 1;

                        $kode = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

                        $set('kode_barang', $kode);

                        if ($state === 'bhp') {
                            $set('kategori_aset_id', null);
                            $set('umur_ekonomis', null);
                            $set('tarif_penyusutan_display', null);
                            $set('nilai_residu', null);
                            $set('status_penggunaan', null);
                            $set('tanggal_diterima', null);
                            $set('jenis_aset', null);
                            $set('jenis_bhp', BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR);
                            $set('satuan', 'Pcs');
                        }

                        if ($state === 'aset') {
                            $set('jenis_bhp', null);
                            $set('satuan', 'Unit');
                            $set('status_penggunaan', BarangKantor::STATUS_SIAP_DIGUNAKAN);
                        }
                    }),

                TextInput::make('kode_barang')
                    ->label('Kode Barang')
                    ->markAsRequired()
                    ->rule('required')
                    ->readOnly()
                    ->disabled(fn (string $operation, $get) => $operation === 'edit' && $get('kategori_barang') === 'aset')
                    ->validationMessages([
                        'required' => 'Kode barang wajib diisi.',
                    ]),
                Select::make('status_penggunaan')
                    ->label('Status Penggunaan')
                    ->options([
                        BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN => 'Belum Siap Digunakan',
                        BarangKantor::STATUS_SIAP_DIGUNAKAN => 'Siap Digunakan',
                    ])
                    ->helperText('Aset mulai disusutkan berdasarkan Tanggal Diterima saat statusnya Siap Digunakan.')
                    ->default(BarangKantor::STATUS_SIAP_DIGUNAKAN)
                    ->native(false)
                    ->live()
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'aset')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset' ? 'required' : 'nullable')
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->disabled(fn (string $operation, ?BarangKantor $record) => $operation === 'create'
                        || BarangKantorResource::isStatusPenggunaanLocked($record))
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'aset')
                    ->afterStateUpdated(function ($state, callable $set, ?BarangKantor $record) {
                        if ($state === BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN) {
                            $set('tanggal_diterima', null);

                            return;
                        }

                        if ($state === BarangKantor::STATUS_SIAP_DIGUNAKAN) {
                            $set('tanggal_diterima', $record?->tanggalPembelianPerolehan()?->toDateString() ?: today()->toDateString());
                        }
                    }),

                DatePicker::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->afterStateHydrated(function ($state, callable $set, ?BarangKantor $record): void {
                        $tanggalPembelian = $record?->tanggalPembelianPerolehan();

                        if (! $tanggalPembelian) {
                            return;
                        }

                        if (blank($state) || Carbon::parse($state)->startOfDay()->lt($tanggalPembelian)) {
                            $set('tanggal_diterima', $tanggalPembelian->toDateString());
                        }
                    })
                    ->minDate(fn (?BarangKantor $record) => $record?->tanggalPembelianPerolehan())
                    ->maxDate(fn (?BarangKantor $record) => $record?->tanggalPembelianPerolehan()
                        ? today()->endOfYear()
                        : today())
                    ->disabled(fn (?BarangKantor $record) => BarangKantorResource::isStatusPenggunaanLocked($record))
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'aset' && $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN)
                    ->rules(fn ($get, ?BarangKantor $record) => [
                        $get('kategori_barang') === 'aset' && $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN ? 'required' : 'nullable',
                        'date',
                        'before_or_equal:' . ($record?->tanggalPembelianPerolehan()
                            ? today()->endOfYear()->toDateString()
                            : today()->toDateString()),
                        ...($record?->tanggalPembelianPerolehan()
                            ? ['after_or_equal:' . $record->tanggalPembelianPerolehan()->toDateString()]
                            : []),
                    ])
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset' && $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN)
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'aset')
                    ->helperText(fn (?BarangKantor $record) => $record?->tanggalPembelianPerolehan()
                        ? 'Tanggal Diterima tidak boleh lebih awal dari tanggal pembelian: ' . $record->tanggalPembelianPerolehan()->format('d/m/Y') . '.'
                        : null)
                    ->validationMessages([
                        'required' => 'Tanggal Diterima wajib diisi.',
                        'after_or_equal' => 'Tanggal Diterima tidak boleh lebih awal dari tanggal pembelian.',
                        'before_or_equal' => 'Tanggal Diterima tidak boleh melebihi batas periode tahun berjalan.',
                    ]),

                Select::make('jenis_aset')
                    ->label('Jenis Aset')
                    ->options([
                        'sarana_pendidikan_laboratorium' => 'Sarana Pendidikan Laboratorium',
                        'inventaris_kantor' => 'Inventaris Kantor',
                        'kendaraan' => 'Kendaraan',
                    ])
                    ->native(false)
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'aset')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset' ? 'required' : 'nullable')
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'aset')
                    ->validationMessages([
                        'required' => 'Jenis aset wajib dipilih.',
                    ]),

                Select::make('jenis_bhp')
                    ->label('Jenis Barang Habis Pakai')
                    ->options([
                        BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR => 'ATK Operasional Kantor',
                        BarangKantor::JENIS_BHP_INVENTARIS_KANTOR => 'BPP Inventaris Kantor',
                    ])
                    ->default(BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR)
                    ->native(false)
                    ->live()
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'bhp')
                    ->rule(fn ($get) => $get('kategori_barang') === 'bhp' ? 'required' : 'nullable')
                    ->visible(fn ($get) => $get('kategori_barang') === 'bhp')
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'bhp')
                    ->validationMessages([
                        'required' => 'Jenis barang habis pakai wajib dipilih.',
                    ]),

                Select::make('kategori_aset_id')
                    ->label('Kategori Aset Tetap')
                    ->options(
                        \App\Models\KategoriAsetTetap::pluck('nama_kategori_aset', 'id_kategori_aset')
                    )
                    ->searchable()
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'aset')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset' ? 'required' : 'nullable')
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->validationMessages([
                        'required' => 'Kategori aset wajib dipilih.',
                    ])
                    ->reactive()
                    ->afterStateHydrated(function ($state, callable $set): void {
                        $kategori = \App\Models\KategoriAsetTetap::find($state);
                        $set('tarif_penyusutan_display', $kategori?->tarif_penyusutan);
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $kategori = \App\Models\KategoriAsetTetap::find($state);
                        $set('umur_ekonomis', $kategori?->umur_ekonomis);
                        $set('tarif_penyusutan_display', $kategori?->tarif_penyusutan);
                    })
                    ->helperText(function ($get) {
                        $id = $get('kategori_aset_id');

                        if (!$id) {
                            return "Pilih kategori aset untuk melihat penjelasan.";
                        }

                        $kategori = \App\Models\KategoriAsetTetap::find($id);
                        return $kategori?->keterangan ?: 'Tidak ada keterangan kategori aset.';
                    }),

                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->markAsRequired()
                    ->rule('required')
                    ->rule('min:3')
                    ->rule('max:50')
                    ->reactive()
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->validationMessages([
                        'required' => 'Nama barang wajib diisi.',
                        'min' => 'Nama barang minimal 3 karakter.',
                        'max' => 'Nama barang maksimal 50 karakter.',
                    ])
                    ->rule(function (string $operation, $get) {
                        if ($operation !== 'create') {
                            return null;
                        }

                        return function (string $attribute, $value, $fail) use ($get) {
                            if ($get('kategori_barang') === 'bhp') {

                                $exists = BarangKantor::where('kategori_barang', 'bhp')
                                    ->where('nama_barang', $value)
                                    ->exists();

                                if ($exists) {
                                    $fail('Nama barang BHP sudah ada dan tidak boleh duplikat.');
                                }
                            }
                        };
                    }),

                TextInput::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->default(1)
                    ->markAsRequired()
                    ->rule('required')
                    ->reactive()
                    ->rule('min:1')
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                        if (blank($state)) {
                            $set('stok', $get('kategori_barang') === 'aset' ? 1 : null);
                        }

                        if ($get('kategori_barang') === 'aset') {
                            $set('stok', 1);
                        }
                    })
                    ->readOnly(fn ($get) => $get('kategori_barang') === 'aset')
                    ->dehydrated(true)
                    ->disabled(fn (string $operation, $get) => $operation === 'edit' && $get('kategori_barang') === 'aset')
                    ->validationMessages([
                        'required' => 'Stok wajib diisi.',
                        'min' => 'Stok harus lebih dari 0.',
                        'numeric' => 'Stok harus berupa angka.',
                    ]),

                Select::make('satuan')
                    ->label('Satuan')
                    ->options([
                        'Pcs'  => 'Pcs',
                        'Unit' => 'Unit',
                        'Pack' => 'Pack',
                        'Kotak' => 'Kotak',
                        'Rim'  => 'Rim',
                    ])
                    ->default('Pcs')
                    ->searchable()
                    ->reactive()
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                        if ($get('kategori_barang') === 'aset' && blank($state)) {
                            $set('satuan', 'Unit');
                        }
                    })
                    ->markAsRequired(fn ($get) => in_array($get('kategori_barang'), ['bhp', 'aset']))
                    ->rule(fn ($get) => in_array($get('kategori_barang'), ['bhp', 'aset']) ? 'required' : 'nullable')
                    ->validationMessages([
                        'required' => 'Satuan wajib dipilih.',
                    ])
                    ->visible(fn ($get) => in_array($get('kategori_barang'), ['bhp', 'aset']))
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(fn ($get) => in_array($get('kategori_barang'), ['bhp', 'aset'])),

                TextInput::make('umur_ekonomis')
                    ->label('Umur Ekonomis (Tahun)')
                    ->readOnly()
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(true),

                TextInput::make('tarif_penyusutan_display')
                    ->label('Tarif Penyusutan')
                    ->suffix('%')
                    ->readOnly()
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->dehydrated(false),

                TextInput::make('nilai_residu')
                    ->label('Nilai Residu')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->dehydrateStateUsing(fn ($state) => BarangKantorResource::normalizeRupiah($state))
                    ->afterStateHydrated(function ($state, callable $set) {
                        $set('nilai_residu', BarangKantorResource::formatRupiah($state));
                    })
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'aset')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset' ? 'required' : 'nullable')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset'
                        ? BarangKantorResource::nonNegativeNominalRule('Nilai residu')
                        : null)
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset'
                        ? BarangKantorResource::residualNotGreaterThanAcquisitionRule($get)
                        : null)
                    ->validationMessages([
                        'required' => 'Nilai residu wajib diisi.',
                        'numeric' => 'Nilai residu harus berupa angka.',
                    ])
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'aset'),

                TextInput::make('harga_perolehan')
                    ->label('Nilai Perolehan (Harga Per Unit)')
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                    ->dehydrateStateUsing(fn ($state) => BarangKantorResource::normalizeRupiah($state))
                    ->afterStateHydrated(function ($state, callable $set) {
                        $set('harga_perolehan', BarangKantorResource::formatRupiah($state));
                    })
                    ->markAsRequired(fn ($get) => $get('kategori_barang') === 'aset')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset' ? 'required' : 'nullable')
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset'
                        ? BarangKantorResource::nonNegativeNominalRule('Nilai perolehan')
                        : null)
                    ->rule(fn ($get) => $get('kategori_barang') === 'aset'
                        ? BarangKantorResource::positiveNominalRangeRule('Nilai perolehan')
                        : null)
                    ->validationMessages([
                        'required' => 'Nilai perolehan wajib diisi.',
                        'numeric' => 'Nilai perolehan harus berupa angka.',
                    ])
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'aset'),

                FileUpload::make('foto')
                    ->label('Foto Barang')
                    ->disk('public')
                    ->directory('barang-kantor')
                    ->image()
                    ->multiple(false)
                    ->imagePreviewHeight('200')
                    ->downloadable()
                    ->openable()
                    ->dehydrated(true)
                    ->nullable(),

                Select::make('status_barang')
                    ->label('Status Barang')
                    ->options(fn (string $operation, ?BarangKantor $record) => $operation === 'edit'
                        && $record?->kategori_barang === 'aset'
                        && $record->status_barang === BarangKantor::STATUS_TIDAK_AKTIF
                            ? [BarangKantor::STATUS_TIDAK_AKTIF => BarangKantor::STATUS_TIDAK_AKTIF]
                            : [
                                BarangKantor::STATUS_AKTIF       => BarangKantor::STATUS_AKTIF,
                                BarangKantor::STATUS_TIDAK_AKTIF => BarangKantor::STATUS_TIDAK_AKTIF,
                            ])
                    ->default('Aktif')
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->disabled(fn ($context) => $context === 'create')
                    ->dehydrated(true)
                    ->required()
                    ->helperText(function (string $operation, ?BarangKantor $record): string {
                        $catatan = 'Catatan: sebelum aset dinonaktifkan, mohon lakukan penyusutan untuk periode sebelumnya atau cek terlebih dahulu apakah aset sudah disusutkan atau belum.';

                        if (
                            $operation === 'edit'
                            && $record?->kategori_barang === 'aset'
                            && $record->status_barang === BarangKantor::STATUS_TIDAK_AKTIF
                        ) {
                            return $catatan . ' Aset yang sudah Tidak Aktif dianggap pemberhentian aset dan tidak bisa diubah kembali menjadi Aktif.';
                        }

                        return $catatan;
                    }),

                // Status peminjaman aset — bisa diset saat create, permanen jika "Tidak untuk Dipinjamkan"
                Select::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->options(fn (string $operation, ?BarangKantor $record) =>
                        $operation === 'edit'
                        && $record?->status_pinjam === BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN
                            ? [BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN => 'Tidak untuk Dipinjamkan']
                            : [
                                BarangKantor::STATUS_PINJAM_TERSEDIA          => 'Tersedia',
                                BarangKantor::STATUS_PINJAM_DIPINJAM          => 'Sedang Dipinjam',
                                BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN => 'Tidak untuk Dipinjamkan',
                            ]
                    )
                    ->default(BarangKantor::STATUS_PINJAM_TERSEDIA)
                    ->native(false)
                    ->visible(fn ($get) => $get('kategori_barang') === 'aset')
                    ->disabled(fn (string $operation, ?BarangKantor $record) =>
                        $record?->status_pinjam === BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN)
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'aset')
                    ->helperText(fn (string $operation, ?BarangKantor $record) =>
                        $record?->status_pinjam === BarangKantor::STATUS_PINJAM_TIDAK_DIPINJAMKAN
                            ? 'Status "Tidak untuk Dipinjamkan" bersifat permanen dan tidak dapat diubah.'
                            : 'Pilih "Tidak untuk Dipinjamkan" jika aset ini tidak boleh dipinjam. Bersifat permanen.'
                    ),
                // Status Pinjam untuk BPP Inventaris Kantor (mirip dengan Aktif/Tidak Aktif pada aset)
                Select::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->options(fn (string $operation, ?BarangKantor $record) =>
                        $operation === 'edit' && $record?->status_pinjam === BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN
                            ? [BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN => 'Telah Didistribusikan']
                            : [
                                BarangKantor::STATUS_PINJAM_TERSEDIA        => 'Tersedia',
                                BarangKantor::STATUS_PINJAM_DIPINJAM        => 'Sedang Dipinjam',
                                BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN => 'Telah Didistribusikan',
                            ]
                    )
                    ->default(BarangKantor::STATUS_PINJAM_TERSEDIA)
                    ->native(false)
                    ->visible(fn ($get) => $get('kategori_barang') === 'bhp'
                        && $get('jenis_bhp') === BarangKantor::JENIS_BHP_INVENTARIS_KANTOR)
                    ->disabled(fn (string $operation, ?BarangKantor $record) =>
                        $record?->status_pinjam === BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN)
                    ->dehydrated(fn ($get) => $get('kategori_barang') === 'bhp'
                        && $get('jenis_bhp') === BarangKantor::JENIS_BHP_INVENTARIS_KANTOR)
                    ->helperText(fn (?BarangKantor $record) =>
                        $record?->status_pinjam === BarangKantor::STATUS_PINJAM_DIDISTRIBUSIKAN
                            ? 'Status "Telah Didistribusikan" tidak dapat diubah kembali.'
                            : 'Status "Telah Didistribusikan" bersifat permanen dan tidak dapat diubah setelahnya.'
                    )
                    ->validationMessages([
                        'required' => 'Status Pinjam wajib dipilih.',
                    ]),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rule('max:255')
                    ->validationMessages([
                        'max' => 'Keterangan maksimal 255 karakter.',
                    ])
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
