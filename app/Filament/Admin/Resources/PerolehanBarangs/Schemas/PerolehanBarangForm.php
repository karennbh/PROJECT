<?php

namespace App\Filament\Admin\Resources\PerolehanBarangs\Schemas;

use App\Filament\Admin\Resources\PerolehanBarangs\PerolehanBarangResource;
use App\Models\BarangKantor;
use App\Models\KategoriAsetTetap;
use App\Models\PendapatanHibah;
use App\Models\PerolehanBarang;
use App\Models\PerolehanBarangDetail;
use App\Support\KasKecilBalance;
use App\Support\PerolehanBarangAllocator;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Validation\ValidationException;
use Livewire\Component as LivewireComponent;

class PerolehanBarangForm
{
    private const MIN_HARGA_PEROLEHAN = 1000;
    private const MAX_HARGA_PEROLEHAN = 100000000;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['novalidate' => true])
            ->components([
                Select::make('sumber_perolehan')
                    ->label('Sumber Perolehan')
                    ->options(PerolehanBarangResource::sumberPerolehanOptions())
                    ->default(fn () => request()->query('sumber_perolehan') ?: PerolehanBarang::SUMBER_PEMBELIAN)
                    ->native(false)
                    ->live()
                    ->required()
                    ->validationAttribute('sumber perolehan')
                    ->validationMessages([
                        'required' => 'Kolom sumber perolehan wajib dipilih.',
                    ])
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated()
                    ->afterStateUpdated(function ($state, Set $set, Get $get, string $operation): void {
                        if ($operation === 'create') {
                            $set('id_perolehan_barang', PerolehanBarangResource::generatePerolehanId((string) $state));
                        }

                        $set('pendapatan_hibah_id', null);
                        $set('nilai_pengakuan_pendapatan_hibah_uang', 0);

                        if ($state === PerolehanBarang::SUMBER_HIBAH) {
                            $set('status_penggunaan', BarangKantor::STATUS_SIAP_DIGUNAKAN);
                            $set('tanggal_diterima', $get('tanggal_pembelian'));
                        }

                        if ($state !== PerolehanBarang::SUMBER_HIBAH) {
                            $set('status_penggunaan', BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN);
                            $set('tanggal_diterima', null);
                        }

                        if (PerolehanBarangResource::isHibahSource($state)) {
                            $details = collect($get('details') ?? [])
                                ->map(function (array $detail): array {
                                    $detail['kategori_barang'] = 'aset';
                                    $detail['kode_barang'] = null;
                                    $detail['jenis_bhp'] = null;

                                    return $detail;
                                })
                                ->all();

                            $set('details', $details);
                        }
                    }),

                TextInput::make('id_perolehan_barang')
                    ->label('Nomor Perolehan')
                    ->readOnly()
                    ->required()
                    ->validationAttribute('nomor perolehan')
                    ->validationMessages([
                        'required' => 'Kolom nomor perolehan wajib diisi.',
                    ])
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated()
                    ->default(fn () => PerolehanBarangResource::generatePerolehanId((string) (request()->query('sumber_perolehan') ?: PerolehanBarang::SUMBER_PEMBELIAN))),

                Hidden::make('status_penggunaan')
                    ->default(BarangKantor::STATUS_SIAP_DIGUNAKAN)
                    ->dehydrated()
                    ->visible(fn (Get $get) => $get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH),

                TextInput::make('status_penggunaan_display')
                    ->label('Status Penggunaan')
                    ->default('Siap Digunakan')
                    ->readOnly()
                    ->dehydrated(false)
                    ->visible(fn (Get $get) => $get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH)
                    ->afterStateHydrated(function (Set $set, Get $get, ?PerolehanBarang $record = null): void {
                        $status = $record?->status_penggunaan ?: $get('status_penggunaan');

                        $set('status_penggunaan_display', PerolehanBarangResource::statusPenggunaanLabel(
                            $status ?: BarangKantor::STATUS_SIAP_DIGUNAKAN
                        ));
                    })
                    ->helperText('Hibah barang dianggap langsung siap digunakan pada tanggal diterima.'),

                Select::make('status_penggunaan')
                    ->label('Status Penggunaan')
                    ->options(PerolehanBarangResource::statusPenggunaanOptions())
                    ->helperText('Penyusutan hanya dimulai setelah aset berstatus Siap Digunakan dan Tanggal Diterima diisi.')
                    ->default(fn () => request()->query('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH
                        ? BarangKantor::STATUS_SIAP_DIGUNAKAN
                        : BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN)
                    ->native(false)
                    ->live()
                    ->required()
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->visible(fn (Get $get) => $get('sumber_perolehan') !== PerolehanBarang::SUMBER_HIBAH)
                    ->dehydrated()
                    ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                        if ($state === BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN) {
                            $set('tanggal_diterima', null);

                            return;
                        }

                        if (blank($get('tanggal_diterima'))) {
                            $set('tanggal_diterima', $get('tanggal_pembelian') ?: today()->toDateString());
                        }
                    }),

                DatePicker::make('tanggal_pembelian')
                    ->label(fn (Get $get) => $get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH
                        ? 'Tanggal Diterima'
                        : 'Tanggal Pembelian')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->minDate(fn () => self::minimumTanggalPerolehan())
                    ->maxDate(fn () => today())
                    ->live()
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated()
                    ->required()
                    ->rules(fn () => [
                        'date',
                        'after_or_equal:' . self::minimumTanggalPerolehan()->toDateString(),
                        'before_or_equal:' . today()->toDateString(),
                    ])
                    ->validationAttribute(fn (Get $get) => $get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH
                        ? 'tanggal diterima'
                        : 'tanggal pembelian')
                    ->validationMessages([
                        'required' => 'Kolom tanggal pembelian wajib diisi.',
                        'date' => 'Tanggal pembelian harus berupa tanggal yang valid.',
                        'after_or_equal' => 'Tanggal pembelian hanya boleh diisi pada tahun berjalan.',
                        'before_or_equal' => 'Tanggal pembelian tidak boleh melebihi hari ini.',
                    ])
                    ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                        if (
                            PerolehanBarangResource::isHibahUangSource($get('sumber_perolehan'))
                            && ! PerolehanBarangResource::isPendapatanHibahAvailableForTanggal($get('pendapatan_hibah_id'), $state)
                        ) {
                            $set('pendapatan_hibah_id', null);
                            PerolehanBarangResource::fillPendapatanHibahInfo(null, $set);
                        }

                        if ($get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH) {
                            $set('status_penggunaan', BarangKantor::STATUS_SIAP_DIGUNAKAN);
                            $set('tanggal_diterima', $state);

                            return;
                        }

                        if (
                            $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN
                            && (blank($get('tanggal_diterima')) || $get('tanggal_diterima') < $state)
                        ) {
                            $set('tanggal_diterima', $state);
                        }
                    }),

                DatePicker::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->displayFormat('d/m/Y')
                    ->native(false)
                    ->minDate(fn (Get $get) => $get('tanggal_pembelian') ?: self::minimumTanggalPerolehan())
                    ->maxDate(fn () => today())
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated()
                    ->required(fn (Get $get) => $get('sumber_perolehan') !== PerolehanBarang::SUMBER_HIBAH
                        && $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN)
                    ->default(null)
                    ->visible(fn (Get $get) => $get('sumber_perolehan') !== PerolehanBarang::SUMBER_HIBAH
                        && $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN)
                    ->rules(fn (Get $get) => [
                        $get('sumber_perolehan') !== PerolehanBarang::SUMBER_HIBAH
                            && $get('status_penggunaan') !== BarangKantor::STATUS_BELUM_SIAP_DIGUNAKAN ? 'required' : 'nullable',
                        'date',
                        'after_or_equal:' . ($get('tanggal_pembelian') ?: self::minimumTanggalPerolehan()->toDateString()),
                        'before_or_equal:' . today()->toDateString(),
                    ])
                    ->validationAttribute('Tanggal Diterima')
                    ->validationMessages([
                        'required' => 'Kolom Tanggal Diterima wajib diisi.',
                        'date' => 'Tanggal Diterima harus berupa tanggal yang valid.',
                        'after_or_equal' => 'Tanggal Diterima tidak boleh lebih awal dari tanggal pembelian.',
                        'before_or_equal' => 'Tanggal Diterima tidak boleh melebihi hari ini.',
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('foto_nota')
                            ->label('Foto Nota')
                            ->disk('public')
                            ->directory('foto-nota')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('sumber_perolehan')))
                            ->required(fn (Get $get, string $operation) => $operation === 'create' && PerolehanBarangResource::isPembelianSource($get('sumber_perolehan')))
                            ->validationAttribute('foto nota')
                            ->validationMessages([
                                'required' => 'Kolom foto nota wajib diupload.',
                            ]),

                        FileUpload::make('bukti_dokumen_hibah')
                            ->label('Bukti Dokumen Hibah')
                            ->disk('public')
                            ->directory('bukti-hibah')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'])
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->visible(fn (Get $get) => PerolehanBarangResource::isHibahSource($get('sumber_perolehan')))
                            ->required(fn (Get $get, string $operation) => $operation === 'create' && PerolehanBarangResource::isHibahSource($get('sumber_perolehan')))
                            ->validationAttribute('bukti dokumen hibah')
                            ->validationMessages([
                                'required' => 'Kolom bukti dokumen hibah wajib diupload.',
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rule('max:255')
                            ->validationAttribute('keterangan')
                            ->validationMessages([
                                'max' => 'Kolom keterangan maksimal 255 karakter.',
                            ])
                            ->maxLength(255)
                            ->rows(3),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => PerolehanBarangResource::isHibahSource($get('sumber_perolehan')))
                    ->schema([
                        TextInput::make('nama_pemberi_hibah')
                            ->label('Sumber Hibah')
                            ->required(fn (Get $get) => $get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH)
                            ->rule('min:3')
                            ->rule('max:50')
                            ->visible(fn (Get $get) => $get('sumber_perolehan') === PerolehanBarang::SUMBER_HIBAH)
                            ->dehydrated(fn (Get $get) => PerolehanBarangResource::isHibahSource($get('sumber_perolehan')))
                            ->afterStateHydrated(function ($state, Set $set, string $operation, ?PerolehanBarang $record = null): void {
                                if ($operation === 'create' || filled($state)) {
                                    return;
                                }

                                $set('nama_pemberi_hibah', $record?->pendapatanHibah?->sumber_hibah ?: '-');
                            })
                            ->validationAttribute('sumber hibah')
                            ->validationMessages([
                                'required' => 'Kolom sumber hibah wajib diisi.',
                                'min' => 'Kolom sumber hibah minimal 3 karakter.',
                                'max' => 'Kolom sumber hibah maksimal 50 karakter.',
                            ]),
                        Select::make('pendapatan_hibah_id')
                            ->label('Sumber Hibah')
                            ->options(fn (Get $get, ?PerolehanBarang $record = null) => PerolehanBarangResource::pendapatanHibahOptions(
                                $get('tanggal_pembelian'),
                                $record,
                            ))
                            ->default(fn () => request()->query('pendapatan_hibah_id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->required(fn (Get $get) => PerolehanBarangResource::isHibahUangSource($get('sumber_perolehan')))
                            ->visible(fn (Get $get) => PerolehanBarangResource::isHibahUangSource($get('sumber_perolehan')))
                            ->rule(function (Get $get) {
                                return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                    if (
                                        filled($value)
                                        && ! PerolehanBarangResource::isPendapatanHibahAvailableForTanggal($value, $get('tanggal_pembelian'))
                                    ) {
                                        $fail('Sumber hibah hanya boleh dipilih jika tanggal hibah sama atau sebelum tanggal pembelian.');
                                    }
                                };
                            })
                            ->afterStateHydrated(function ($state, Set $set): void {
                                PerolehanBarangResource::fillPendapatanHibahInfo($state, $set);
                            })
                            ->afterStateUpdated(function ($state, Set $set): void {
                                PerolehanBarangResource::fillPendapatanHibahInfo($state, $set);
                            })
                            ->validationMessages([
                                'required' => 'Sumber hibah wajib dipilih.',
                            ]),

                        TextInput::make('total_pendapatan_hibah_display')
                            ->label('Total Pendapatan Hibah')
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated(false)
                            ->visible(fn (Get $get) => PerolehanBarangResource::isHibahUangSource($get('sumber_perolehan')))
                            ->afterStateHydrated(function ($state, Set $set, Get $get): void {
                                PerolehanBarangResource::fillPendapatanHibahInfo($get('pendapatan_hibah_id'), $set);
                            }),

                        Hidden::make('nilai_pengakuan_pendapatan_hibah_uang')
                            ->default(0)
                            ->dehydrated(),
                    ]),

                Repeater::make('details')
                    ->label('Daftar Barang')
                    ->relationship()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->collapsible()
                    ->collapsed(false)
                    ->grid(2)
                    ->columnSpanFull()
                    ->addable(fn (string $operation) => $operation !== 'edit')
                    ->deletable(fn (string $operation) => $operation !== 'edit')
                    ->schema([
                        TextInput::make('kategori_barang_hibah_label')
                            ->label('Kategori Barang')
                            ->default('Aset Tetap')
                            ->readOnly()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set): void {
                                $set('kategori_barang_hibah_label', 'Aset Tetap');
                            })
                            ->visible(fn (Get $get) => PerolehanBarangResource::isHibahSource($get('../../sumber_perolehan'))),

                        Hidden::make('kategori_barang')
                            ->default('aset')
                            ->dehydrated(),

                        Select::make('kategori_barang_input')
                            ->label('Kategori Barang')
                            ->options(PerolehanBarangResource::kategoriBarangOptions())
                            ->default('aset')
                            ->native(false)
                            ->reactive()
                            ->required()
                            ->selectablePlaceholder(false)
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan')))
                            ->rule(function (Get $get) {
                                return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                    if (PerolehanBarangResource::isHibahSource($get('../../sumber_perolehan')) && $value !== 'aset') {
                                        $fail('Perolehan hibah hanya boleh untuk Aset Tetap.');
                                    }
                                };
                            })
                            ->validationAttribute('kategori barang')
                            ->validationMessages([
                                'required' => 'Kolom kategori barang wajib dipilih.',
                            ])
                            ->afterStateHydrated(function (Set $set, Get $get): void {
                                $set('kategori_barang_input', $get('kategori_barang') ?: 'aset');
                            })
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $set('kategori_barang', $state ?: 'aset');

                                if ($state === 'bhp') {
                                    $set('kategori_aset_id', null);
                                    $set('umur_ekonomis', null);
                                    $set('tarif_penyusutan_display', null);
                                    $set('jenis_aset', null);
                                    $set('jenis_bhp', BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR);
                                }

                                if ($state === 'aset') {
                                    $set('jenis_bhp', null);
                                }
                            })
                            ->disabled(fn (Get $get, string $operation) => $operation === 'edit' || PerolehanBarangResource::isHibahSource($get('../../sumber_perolehan')))
                            ->dehydrated(false),

                        TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->required(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->rule('min:3')
                            ->rule('max:50')
                            ->validationAttribute('nama barang')
                            ->validationMessages([
                                'required' => 'Kolom nama barang wajib diisi.',
                                'min' => 'Kolom nama barang minimal 3 karakter.',
                                'max' => 'Kolom nama barang maksimal 50 karakter.',
                            ])
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->dehydrated(),

                        Select::make('jenis_aset')
                            ->label('Jenis Aset')
                            ->options(PerolehanBarangResource::jenisAsetOptions())
                            ->native(false)
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->required(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->validationAttribute('jenis aset')
                            ->validationMessages([
                                'required' => 'Kolom jenis barang wajib dipilih.',
                            ]),

                        Select::make('jenis_bhp')
                            ->label('Jenis Barang Habis Pakai')
                            ->options(PerolehanBarangResource::jenisBhpOptions())
                            ->default(BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR)
                            ->native(false)
                            ->live()
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'bhp')
                            ->required(fn (Get $get) => $get('kategori_barang') === 'bhp')
                            ->afterStateUpdated(function (Set $set): void {
                                $set('kode_barang', null);
                            })
                            ->validationAttribute('jenis barang habis pakai')
                            ->validationMessages([
                                'required' => 'Kolom jenis barang habis pakai wajib dipilih.',
                            ]),

                        Select::make('kategori_aset_id')
                            ->label('Kategori Aset Tetap')
                            ->options(KategoriAsetTetap::pluck('nama_kategori_aset', 'id_kategori_aset'))
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->required(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->validationAttribute('kategori aset tetap')
                            ->validationMessages([
                                'required' => 'Kolom kategori aset tetap wajib dipilih.',
                            ])
                            ->afterStateUpdated(function ($state, Set $set): void {
                                $kategori = KategoriAsetTetap::query()->find($state);
                                $set('umur_ekonomis', $kategori?->umur_ekonomis);
                                $set('tarif_penyusutan_display', $kategori?->tarif_penyusutan);
                            })
                            ->afterStateHydrated(function ($state, Set $set): void {
                                $kategori = KategoriAsetTetap::query()->find($state);
                                $set('tarif_penyusutan_display', $kategori?->tarif_penyusutan);
                            })
                            ->helperText(function (Get $get): string {
                                $kategoriId = $get('kategori_aset_id');

                                if (blank($kategoriId)) {
                                    return 'Pilih kategori aset untuk melihat penjelasan.';
                                }

                                return KategoriAsetTetap::query()->find($kategoriId)?->keterangan
                                    ?: 'Tidak ada keterangan kategori aset.';
                            }),

                        TextInput::make('umur_ekonomis')
                            ->label('Umur Ekonomis (Tahun)')
                            ->numeric()
                            ->rule('min:0')
                            ->readOnly()
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->validationAttribute('umur ekonomis')
                            ->validationMessages([
                                'min' => 'Umur ekonomis tidak boleh kurang dari 0.',
                                'numeric' => 'Umur ekonomis harus berupa angka.',
                            ]),

                        TextInput::make('tarif_penyusutan_display')
                            ->label('Tarif Penyusutan')
                            ->suffix('%')
                            ->readOnly()
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->dehydrated(false),

                        self::rupiahInput('nilai_residu', 'Nilai Residu')
                            ->required(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->rule(fn (Get $get) => self::residualNotGreaterThanAcquisitionRule($get))
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'aset')
                            ->validationAttribute('nilai residu')
                            ->validationMessages([
                                'required' => 'Kolom nilai residu wajib diisi.',
                            ]),

                        Select::make('kode_barang')
                            ->label('Nama Barang (BHP)')
                            ->options(fn (Get $get) => BarangKantor::query()
                                ->where('kategori_barang', 'bhp')
                                ->where('jenis_bhp', $get('jenis_bhp') ?: BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR)
                                ->orderBy('nama_barang')
                                ->get()
                                ->mapWithKeys(fn (BarangKantor $barang) => [
                                    $barang->kode_barang => "{$barang->nama_barang} - {$barang->jenis_barang_label}",
                                ]))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get) => $get('kategori_barang') === 'bhp')
                            ->required(fn (Get $get) => $get('kategori_barang') === 'bhp')
                            ->validationAttribute('nama barang BHP')
                            ->validationMessages([
                                'required' => 'Kolom nama barang BHP wajib dipilih.',
                            ])
                            ->afterStateUpdated(function ($state, Set $set): void {
                                $barang = BarangKantor::query()->find($state);
                                $set('jenis_bhp', $barang?->jenis_bhp ?: BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR);
                                // Set satuan perolehan default = satuan master data
                                $set('satuan_perolehan', $barang?->satuan ?: PerolehanBarangDetail::SATUAN_PEROLEHAN_UNIT);
                            })
                            ->dehydrated()
                            ->createOptionForm([
                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->rule('min:3')
                                    ->rule('max:50')
                                    ->validationAttribute('nama barang')
                                    ->validationMessages([
                                        'required' => 'Kolom nama barang wajib diisi.',
                                        'min' => 'Kolom nama barang minimal 3 karakter.',
                                        'max' => 'Kolom nama barang maksimal 50 karakter.',
                                    ]),
                                Select::make('satuan')
                                    ->label('Satuan')
                                    ->options(PerolehanBarangResource::satuanBarangOptions())
                                    ->default('Pcs')
                                    ->native(false)
                                    ->required(),
                                Select::make('jenis_bhp')
                                    ->label('Jenis Barang Habis Pakai')
                                    ->options(PerolehanBarangResource::jenisBhpOptions())
                                    ->default(BarangKantor::JENIS_BHP_ATK_OPERASIONAL_KANTOR)
                                    ->native(false)
                                    ->required(),
                                Textarea::make('keterangan')
                                    ->rows(2)
                                    ->rule('max:255')
                                    ->validationAttribute('keterangan')
                                    ->validationMessages([
                                        'max' => 'Kolom keterangan maksimal 255 karakter.',
                                    ])
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data, Set $set) {
                                $last = BarangKantor::where('kategori_barang', 'bhp')->orderByDesc('kode_barang')->value('kode_barang');
                                $next = $last ? intval(substr($last, 4)) + 1 : 1;

                                $barang = BarangKantor::create([
                                    'kategori_barang' => 'bhp',
                                    'kode_barang' => 'BHP-' . str_pad($next, 5, '0', STR_PAD_LEFT),
                                    'nama_barang' => $data['nama_barang'],
                                    'jenis_bhp' => $data['jenis_bhp'],
                                    'stok' => 0,
                                    'satuan' => $data['satuan'],
                                    'keterangan' => $data['keterangan'] ?? null,
                                    'status_barang' => 'Aktif',
                                ]);

                                $set('jenis_bhp', $barang->jenis_bhp);
                                $set('satuan_perolehan', $barang->satuan ?: PerolehanBarangDetail::SATUAN_PEROLEHAN_UNIT);

                                return $barang->kode_barang;
                            }),

                        Select::make('satuan_perolehan')
                            ->label('Satuan Perolehan')
                            ->options(function (Get $get): array {
                                $kode = $get('kode_barang');
                                if (blank($kode)) {
                                    // Barang baru: semua opsi tersedia
                                    return PerolehanBarangDetail::satuanPerolehanOptions();
                                }
                                $barang = BarangKantor::query()->find($kode);
                                if (! $barang) {
                                    return PerolehanBarangDetail::satuanPerolehanOptions();
                                }
                                // Barang sudah ada: opsi berdasarkan satuan master data
                                return PerolehanBarangDetail::satuanPerolehanOptionsForSatuan($barang->satuan);
                            })
                            ->default(PerolehanBarangDetail::SATUAN_PEROLEHAN_UNIT)
                            ->native(false)
                            ->live()
                            ->required()
                            ->helperText(function (Get $get): ?string {
                                $kode    = $get('kode_barang');
                                $satuan  = $get('satuan_perolehan');
                                if (blank($kode)) {
                                    return null;
                                }
                                $barang = BarangKantor::query()->find($kode);
                                if (! $barang) {
                                    return null;
                                }
                                if ($satuan === PerolehanBarangDetail::SATUAN_PEROLEHAN_BOX) {
                                    return '1 Box = ' . PerolehanBarangDetail::KONVERSI_BOX_KE_RIM
                                        . ' Rim → stok master data akan ditambah dalam satuan Rim.';
                                }
                                return 'Satuan master data: ' . $barang->satuan . '.';
                            })
                            ->validationAttribute('satuan perolehan')
                            ->validationMessages([
                                'required' => 'Kolom satuan perolehan wajib dipilih.',
                            ]),

                        TextInput::make('jumlah_perolehan')
                            ->label('Jumlah')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->rule('gt:0')
                            ->validationAttribute('jumlah')
                            ->validationMessages([
                                'required' => 'Kolom jumlah wajib diisi.',
                                'gt' => 'Jumlah harus lebih dari 0.',
                                'numeric' => 'Jumlah harus berupa angka.',
                            ])
                            ->afterStateUpdatedJs(<<<'JS'
                                const jumlah = Math.max(1, parseInt(($state ?? 0).toString().replace(/[^0-9]/g, '')) || 0)
                                const parseSignedNominal = (value) => {
                                    const raw = (value ?? '').toString().replace(/Rp/g, '').replace(/[.\s,]/g, '')
                                    if (raw === '' || raw === '-') return 0
                                    const sign = raw.startsWith('-') ? -1 : 1
                                    const digits = raw.replace(/[^0-9]/g, '')
                                    return sign * (parseInt(digits || '0', 10) || 0)
                                }
                                const hargaBarang = parseSignedNominal($get('harga_satuan'))
                                const hargaPerolehan = parseSignedNominal($get('harga_perolehan'))
                                $set('total_harga', new Intl.NumberFormat('id-ID').format(jumlah * hargaBarang))
                                $set('total_harga_perolehan', new Intl.NumberFormat('id-ID').format(jumlah * hargaPerolehan))
                            JS),

                        self::rupiahInput('harga_satuan', 'Harga Satuan')
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan')))
                            ->required(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan')))
                            ->validationAttribute('harga satuan')
                            ->validationMessages([
                                'required' => 'Kolom harga satuan wajib diisi.',
                            ])
                            ->rule(fn () => self::positiveNominalRangeRule('Harga satuan'))
                            ->afterStateUpdatedJs(<<<'JS'
                                const jumlah = Math.max(1, parseInt(($get('jumlah_perolehan') ?? 0).toString().replace(/[^0-9]/g, '')) || 0)
                                const raw = ($state ?? '').toString().replace(/Rp/g, '').replace(/[.\s,]/g, '')
                                const sign = raw.startsWith('-') ? -1 : 1
                                const harga = sign * (parseInt(raw.replace(/[^0-9]/g, '') || '0', 10) || 0)
                                $set('total_harga', new Intl.NumberFormat('id-ID').format(jumlah * harga))
                            JS),

                        self::rupiahInput('total_harga', 'Total Harga', true)
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan'))),

                        TextInput::make('persentase_subtotal')
                            ->label('Persentase Subtotal')
                            ->suffix('%')
                            ->readOnly()
                            ->default(0)
                            ->dehydrateStateUsing(fn ($state) => is_numeric($state) ? (float) $state : 0)
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan'))),

                        self::rupiahInput('alokasi_diskon', 'Alokasi Diskon', true)
                            ->default(0)
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan'))),

                        self::rupiahInput('alokasi_biaya_lainnya', 'Alokasi Biaya Lainnya', true)
                            ->default(0)
                            ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan'))),

                        self::rupiahInput(
                            'harga_perolehan',
                            'Harga Perolehan',
                            fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('../../sumber_perolehan'))
                        )
                            ->required()
                            ->validationAttribute('harga perolehan')
                            ->validationMessages([
                                'required' => 'Kolom harga perolehan wajib diisi.',
                            ])
                            ->rule(fn () => self::positiveNominalRangeRule('Harga perolehan'))
                            ->rule(fn (Get $get, $record = null) => self::hibahUangItemLimitRule($get, $record))
                            ->afterStateUpdatedJs(<<<'JS'
                                const jumlah = Math.max(1, parseInt(($get('jumlah_perolehan') ?? 0).toString().replace(/[^0-9]/g, '')) || 0)
                                const raw = ($state ?? '').toString().replace(/Rp/g, '').replace(/[.\s,]/g, '')
                                const sign = raw.startsWith('-') ? -1 : 1
                                const harga = sign * (parseInt(raw.replace(/[^0-9]/g, '') || '0', 10) || 0)
                                $set('total_harga_perolehan', new Intl.NumberFormat('id-ID').format(jumlah * harga))
                            JS),

                        self::rupiahInput('total_harga_perolehan', 'Total Harga Perolehan', true),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => PerolehanBarangResource::isPembelianSource($get('sumber_perolehan')))
                    ->schema([
                        self::rupiahInput('subtotal_barang', 'Subtotal Harga Barang', true)->default(0),
                        self::rupiahInput('diskon_total', 'Diskon')
                            ->default(0),
                        self::rupiahInput('biaya_lainnya_total', 'Biaya Lainnya')
                            ->default(0),
                        self::rupiahInput('grand_total', 'Grand Total', true)
                            ->default(0)
                            ->columnSpanFull(),
                        Placeholder::make('alokasi_info')
                            ->label('Catatan Perhitungan')
                            ->content('Isi daftar barang, Diskon, dan Biaya Lainnya sesuai nota, lalu klik "Hitung Alokasi". Diskon mengurangi harga perolehan, biaya lainnya menambah harga perolehan.')
                            ->columnSpanFull(),
                        Actions::make([
                            Action::make('hitungAlokasi')
                                ->label('Hitung Alokasi')
                                ->color('primary')
                                ->visible(fn (string $operation) => $operation !== 'view')
                                ->action(function (Get $get, Set $set, LivewireComponent $livewire, ?PerolehanBarang $record = null) {
                                    self::clearKasKecilBalanceError($livewire);

                                    $detailsState = $get('details') ?? [];

                                    $sourceData = [
                                        'details' => array_values($detailsState),
                                        'diskon_total' => $get('diskon_total'),
                                        'biaya_lainnya_total' => $get('biaya_lainnya_total'),
                                    ];

                                    $errors = self::invalidAllocationInputMessages($sourceData);

                                    if ($errors !== []) {
                                        throw ValidationException::withMessages($errors);
                                    }

                                    $data = PerolehanBarangAllocator::allocate($sourceData);
                                    self::validateKasKecilBalance(
                                        (int) ($data['grand_total'] ?? 0),
                                        $record?->id_perolehan_barang
                                    );
                                    self::clearKasKecilBalanceError($livewire);

                                    $formattedDetails = collect($data['details'])->map(fn (array $detail) => array_merge($detail, [
                                        'harga_satuan' => self::formatRupiah($detail['harga_satuan'] ?? 0),
                                        'total_harga' => self::formatRupiah($detail['total_harga'] ?? 0),
                                        'alokasi_diskon' => self::formatRupiah($detail['alokasi_diskon'] ?? 0),
                                        'alokasi_biaya_lainnya' => self::formatRupiah($detail['alokasi_biaya_lainnya'] ?? 0),
                                        'harga_perolehan' => self::formatRupiah($detail['harga_perolehan'] ?? 0),
                                        'total_harga_perolehan' => self::formatRupiah($detail['total_harga_perolehan'] ?? 0),
                                    ]))->all();

                                    if (! array_is_list($detailsState)) {
                                        $formattedDetails = array_combine(
                                            array_keys($detailsState),
                                            array_slice($formattedDetails, 0, count($detailsState))
                                        ) ?: $formattedDetails;
                                    }

                                    $set('details', $formattedDetails);
                                    $set('subtotal_barang', self::formatRupiah($data['subtotal_barang'] ?? 0));
                                    $set('grand_total', self::formatRupiah($data['grand_total'] ?? 0));
                                }),
                        ])->columnSpanFull(),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => PerolehanBarangResource::isHibahSource($get('sumber_perolehan')))
                    ->schema([
                        self::rupiahInput('total_nilai_hibah', 'Total Nilai Hibah', true)
                            ->default(0)
                            ->columnSpanFull(),
                        Placeholder::make('hibah_info')
                            ->label('Catatan Hibah')
                            ->content('Untuk hibah, cukup isi nilai perolehan final tiap barang. Tidak perlu mengisi diskon, kupon, ongkir, asuransi, atau biaya layanan.')
                            ->columnSpanFull(),
                        Actions::make([
                            Action::make('hitungNilaiHibah')
                                ->label('Hitung Nilai Hibah')
                                ->color('primary')
                                ->visible(fn (string $operation) => $operation !== 'view')
                                ->action(function (Get $get, Set $set) {
                                    $total = (int) collect($get('details') ?? [])
                                        ->sum(fn (array $detail) => self::normalizeRupiah($detail['total_harga_perolehan'] ?? 0));
                                    $set('total_nilai_hibah', self::formatRupiah($total));
                                }),
                        ])->columnSpanFull(),
                    ]),
            ]);
    }

    private static function rupiahInput(string $name, string|\Closure $label, bool|\Closure $readOnly = false): TextInput
    {
        $input = TextInput::make($name)
            ->label($label)
            ->prefix('Rp')
            ->dehydrateStateUsing(fn ($state) => self::normalizeRupiah($state))
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'autocomplete' => 'off',
                'style' => 'text-align: right',
            ])
            ->readOnly($readOnly)
            ->rule(fn () => self::nonNegativeNominalRule(is_string($label) ? $label : 'Nominal'));

        if ($readOnly === true) {
            $input->afterStateHydrated(function ($state, callable $set) use ($name) {
                $set($name, self::formatRupiah($state));
            });
        } else {
            $input
                ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                ->afterStateHydrated(function ($state, callable $set) use ($name) {
                    if (blank($state)) {
                        $set($name, null);

                        return;
                    }

                    $set($name, self::formatRupiah($state));
                });
        }

        return $input;
    }

    private static function normalizeRupiah(mixed $state): int
    {
        if ($state === null || $state === '') {
            return 0;
        }

        $numeric = str_replace(['.', ',', ' ', 'Rp'], '', trim((string) $state));

        return (int) ($numeric !== '' ? $numeric : 0);
    }

    private static function formatRupiah(mixed $state): string
    {
        return number_format(self::normalizeRupiah($state), 0, ',', '.');
    }

    private static function minimumTanggalPerolehan(): \Illuminate\Support\Carbon
    {
        return today()->startOfYear();
    }

    private static function nonNegativeNominalRule(string $fieldLabel): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($fieldLabel): void {
            if ($value === null || $value === '') {
                return;
            }

            if (str_contains((string) $value, '-')) {
                $fail($fieldLabel . ' tidak boleh input kurang dari 0.');

                return;
            }

            if (self::containsRepeatedZeroInput($value)) {
                $fail($fieldLabel . ' tidak boleh menggunakan 0 ganda.');

                return;
            }

        };
    }

    private static function positiveNominalRangeRule(string $fieldLabel): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($fieldLabel): void {
            if ($value === null || $value === '') {
                return;
            }

            if (str_contains((string) $value, '-')) {
                $fail($fieldLabel . ' harus lebih dari 0.');

                return;
            }

            $nominal = self::normalizeRupiah($value);

            if ($nominal <= 0) {
                $fail($fieldLabel . ' harus lebih dari 0.');

                return;
            }

            if ($nominal < self::MIN_HARGA_PEROLEHAN) {
                $fail($fieldLabel . ' minimal Rp 1.000.');

                return;
            }

            if ($nominal > self::MAX_HARGA_PEROLEHAN) {
                $fail($fieldLabel . ' tidak boleh lebih dari Rp 100.000.000.');
            }
        };
    }

    private static function residualNotGreaterThanAcquisitionRule(callable $get): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
            if ($value === null || $value === '') {
                return;
            }

            $nilaiResidu = self::normalizeRupiah($value);
            $hargaPerolehan = self::normalizeRupiah($get('harga_perolehan'));

            if ($hargaPerolehan > 0 && $nilaiResidu > $hargaPerolehan) {
                $fail('Nilai residu tidak boleh melebihi harga perolehan.');
            }
        };
    }

    private static function hibahUangItemLimitRule(Get $get, mixed $record = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
            if (! PerolehanBarangResource::isHibahUangSource($get('../../sumber_perolehan'))) {
                return;
            }

            $hibah = PendapatanHibah::query()->find($get('../../pendapatan_hibah_id'));

            if (! $hibah) {
                return;
            }

            $jumlah = max(1, (int) ($get('jumlah_perolehan') ?? 1));
            $nominal = self::normalizeRupiah($value) * $jumlah;
            $perolehanId = $record instanceof PerolehanBarang
                ? $record->id_perolehan_barang
                : ($get('../../id_perolehan_barang') ?: null);
            $sisa = $perolehanId
                ? max(0, (int) $hibah->nilai_hibah - $hibah->usedAmount($perolehanId))
                : $hibah->sisa;

            if ($nominal > $sisa) {
                $fail('Harga perolehan tidak boleh melebihi sisa dana hibah yang tersedia. Sisa dana hibah saat ini sebesar Rp'
                    . number_format($sisa, 0, ',', '.')
                    . '.');
            }
        };
    }

    private static function invalidAllocationInputMessages(array $sourceData): array
    {
        $errors = [];
        $allocationNominalLabels = [
            'diskon_total' => 'Diskon',
            'biaya_lainnya_total' => 'Biaya lainnya',
        ];

        foreach ($allocationNominalLabels as $field => $label) {
            if (self::isTrulyEmpty($sourceData[$field] ?? null)) {
                $errors["data.{$field}"] = $label . ' tidak boleh kosong.';

                continue;
            }

            if (self::containsNegativeSign($sourceData[$field] ?? null)) {
                $errors["data.{$field}"] = $label . ' tidak boleh input kurang dari 0.';

                continue;
            }

            if (self::containsRepeatedZeroInput($sourceData[$field] ?? null)) {
                $errors["data.{$field}"] = $label . ' tidak boleh menggunakan 0 ganda.';

                continue;
            }

        }

        $subtotal = collect($sourceData['details'] ?? [])
            ->sum(function (array $detail): int {
                $jumlah = max(1, (int) ($detail['jumlah_perolehan'] ?? 1));

                return $jumlah * self::normalizeRupiah($detail['harga_satuan'] ?? $detail['harga_perolehan'] ?? 0);
            });
        $diskon = self::normalizeRupiah($sourceData['diskon_total'] ?? 0);
        $biayaLainnya = self::normalizeRupiah($sourceData['biaya_lainnya_total'] ?? 0);

        if ($subtotal > 0 && $diskon >= ($subtotal + $biayaLainnya)) {
            $errors['data.diskon_total'] = 'Diskon tidak boleh lebih besar atau sama dengan subtotal barang ditambah biaya lainnya.';
        }

        foreach (($sourceData['details'] ?? []) as $index => $detail) {
            if (($detail['jumlah_perolehan'] ?? null) !== null && (int) $detail['jumlah_perolehan'] <= 0) {
                $errors["data.details.{$index}.jumlah_perolehan"] = 'Jumlah produk harus melebihi 0.';

                continue;
            }

            foreach ([
                'harga_satuan' => 'Harga satuan',
                'harga_perolehan' => 'Harga perolehan',
                'nilai_residu' => 'Nilai residu',
            ] as $field => $label) {
                if (self::containsNegativeSign($detail[$field] ?? null)) {
                    $errors["data.details.{$index}.{$field}"] = $label . ' tidak boleh input kurang dari 0.';
                }
            }
        }

        return $errors;
    }

    private static function isTrulyEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    private static function containsNegativeSign(mixed $value): bool
    {
        return is_string($value) && str_contains($value, '-');
    }

    private static function containsRepeatedZeroInput(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $normalized = str_replace([' ', '.', ',', 'Rp'], '', trim($value));

        return $normalized !== '' && $normalized !== '0' && trim($normalized, '0') === '';
    }

    private static function validateKasKecilBalance(int $needed, ?string $excludePerolehanId = null): void
    {
        $available = KasKecilBalance::available($excludePerolehanId);

        if ($needed > $available) {
            throw ValidationException::withMessages([
                'data.grand_total' => 'Saldo Kas Kecil tidak mencukupi. Silakan melakukan Pengisian Kas Kecil terlebih dahulu. Sisa saldo: Rp'
                    . number_format($available, 0, ',', '.')
                    . ', nilai perolehan: Rp'
                    . number_format($needed, 0, ',', '.')
                    . '.',
            ]);
        }
    }

    private static function clearKasKecilBalanceError(LivewireComponent $livewire): void
    {
        $livewire->resetValidation('data.grand_total');
    }
}
